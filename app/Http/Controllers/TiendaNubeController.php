<?php

namespace App\Http\Controllers;
ini_set('max_execution_time', 3600);

use Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

use App\ShoeDetail;
use App\ShoeSucursalItem;
use App\Shoe;
use App\Sucursal;
use App\OrderTiendanube;
use App\OrderItem;
use App\Order;
use App\TiendanubeError;
use App\MappingTiendanube;
use App\TiendanubeUpdateRun;
use App\TiendaNubeStockError;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;

class TiendaNubeController extends Controller
{
    const LOG_LABEL = "[TIENDANUBE API]";
    const TIENDA_NUBE_API_URL = "https://api.tiendanube.com/v1/1153537/";
    const TIENDA_NUBE_USER_ID = 9;
    const ORDER_STATUS = ['created' => 0, 'fulfilled' => 1, 'cancelled' => 2];
    const TIENDANUBE_START_STORE_ID = '1153537';

    public function order_created(Request $request) {
        Log::info(self::LOG_LABEL. " New order created: ". $request->getContent());

        Log::info(self::LOG_LABEL. " Request order data to tiendanube.");

        $id_order = $request->input('id', null);
        $order = OrderTiendanube::where('id_tiendanube', $id_order)->first();
        if ($order) {
            Log::info(self::LOG_LABEL." WARN. Order was already created");
            return 200;
        }

        $response = $this->sendRequest("orders/{$id_order}");

        if ($response['products'] && count($response['products'])) {
            $comments = '';
            $total_order_qty = 0;
            try {
                DB::beginTransaction();

                $new_order = Order::create([
                                'id_user' => self::TIENDA_NUBE_USER_ID,
                                'id_client' => null,
                                'order_type' => 'App\OrderTiendanube',
                                'qty' => $total_order_qty,
                                'subtotal' => $response['subtotal'],
                                'total' => $response['total'],
                            ]);

                //TODO CREAR ORDERITEMS!
                $type_order = new OrderTiendanube();
                $type_order->id = $new_order->order_id;
                $type_order->id_tiendanube = $response['id'];
                $type_order->number_tiendanube = $response['number'];
                $type_order->shipping_cost_owner = $response['shipping_cost_owner'];
                $type_order->shipping_cost_customer = $response['shipping_cost_customer'];
                $type_order->shipping_option = $response['shipping_option'];
                $type_order->discount = $response['discount'];
                $type_order->gateway = $response['gateway'];
                $type_order->comments = $comments;
                $type_order->status = 0;

                $type_order->save();
                DB::commit();

                foreach($response['products'] as $product) {
                    try {
                        Log::info(self::LOG_LABEL." Check Stock for product {$product['sku']}");
    
                        $qty = $product['quantity'];
                        $total_order_qty += $qty;
                        $shoe_detail = ShoeDetail::findOrFail($product['sku']);
                        
                        if ($shoe_detail->stock > $product['quantity']) {
                            Log::info(self::LOG_LABEL." Verify stock in Start Calzados");
                            $start_item = ShoeSucursalItem::where([
                                ['id_shoe_detail', $shoe_detail->id],
                                ['id_sucursal', Sucursal::ID_START_CALZADOS]])->first();
                            
                            
                            if ($start_item->stock > 0) {

                                $order_item = new OrderItem();
                                $order_item->id_order = $new_order->order_id;
                                $order_item->sucursal = "Start Calzados";
                                $order_item->id_shoe_detail = $shoe_detail->id;
                                $order_item->sell_price = $product['price'];
                                $order_item->buy_price = $shoe_detail->buy_price;

                                //si están todas en start
                                if ($start_item->stock > $qty) {
                                    $start_item->stock -= $qty;
                                    $order_item->qty = $qty;
                                    $qty = 0;
                                    Log::info(self::LOG_LABEL." Start Calzados has stock.");
                                }
                                //sino poner 0 stock y buscar 
                                else {
                                    $qty -= $start_item->stock;
                                    $order_item->qty = $start_item->stock;
                                    $start_item->stock = 0;
                                    Log::info(self::LOG_LABEL." Start Calzados has not enough stock.");
                                }
                                $start_item->save();
                                $order_item->total = $order_item->sell_price * $order_item->qty;
                                $order_item->save();
                            }
                            if ($qty > 0) {
                                Log::info(self::LOG_LABEL." Verify stock in other sucursals.");
                                                                
                                $sucursal_items = ShoeSucursalItem::select('shoe_sucursal_items.*', 'sucursals.name as name')
                                    ->join('sucursals', 'shoe_sucursal_items.id_sucursal', 'sucursals.id')
                                    ->where([
                                        ['id_shoe_detail', $shoe_detail->id],
                                        ['id_sucursal', '<>', Sucursal::ID_START_CALZADOS]])
                                    ->get();
                                
                                foreach ($sucursal_items as $item) {
                                    if ($item->stock > 0) {

                                        $order_item = new OrderItem();
                                        $order_item->id_order = $new_order->order_id;
                                        $order_item->sucursal = $item->name;
                                        $order_item->id_shoe_detail = $shoe_detail->id;
                                        $order_item->sell_price = $product['price'];
                                        $order_item->buy_price = $shoe_detail->buy_price;

                                        if ($item->stock > $qty) {
                                            $item->stock -= $qty;
                                            $order_item->qty = $qty;
                                            $qty = 0;
                                            $item->save();
                                            break;
                                        }
                                        else {
                                            $qty -= $item->stock;
                                            $order_item->qty = $item->stock;
                                            $item->stock = 0;
                                            $item->save();
                                        }
                                        $order_item->total = $order_item->sell_price * $order_item->qty;
                                        $order_item->save();
                                    }
                                }
                            }
    
                            if ($qty == 0) {
                                Log::info(self::LOG_LABEL." Stock verified.");
                            }
                            else {
                                Log::info(self::LOG_LABEL." There was an error for product {$product['sku']}.");
                                $comments = $comments. "No se encontró todo el stock para art {$product['sku']} (Faltante: {$qty}. ";
                            }
                            $shoe_detail->stock = $shoe_detail->stock - $product['quantity'] + $qty;
                            $shoe_detail->save();
                        }
                    }
                    catch (ModelNotFoundException $e) {
                        Log::error(self::LOG_LABEL." Product {$product['sku']} not found.");
                        $comments = $comments. "No se encontró stock para art {$product['sku']}. ";
                    }
                    catch (Exception $e) {
                        Log::error(self::LOG_LABEL." ERROR. {$e}");
                        $comments = $comments. "Hubo un error para art {$product['sku']}. ";
                    }
                }
                Log::info(self::LOG_LABEL. " SUCCESS. Order has been created.");
                $new_order->qty = $total_order_qty;
                $new_order->save();

            }
            catch (Exception $e) {
                Log::error(self::LOG_LABEL." ERROR. {$e}");
                DB::rollBack();
            }
        }
        return 200;
    }

    public function order_fulfilled(Request $request) {
        Log::info(self::LOG_LABEL. " ". $request->getContent());
    }

    public function order_cancelled(Request $request) {
        Log::info(self::LOG_LABEL. " New request to cancel order received. ". $request->getContent());

        $id_order = $request->input('id', null);
        $order = OrderTiendanube::where('id_tiendanube', $id_order)->firstOrFail();

        Log::info(self::LOG_LABEL. " Order found. ID: {$order->id}.");

        if ($order) {
            try {

                DB::beginTransaction();

                $order_items = OrderItem::where('id_order', $order->id)
                                ->get();

                foreach ($order_items as $item) {
                    Log::info(self::LOG_LABEL. " Restoring stock for order item (id: {$item->id} shoe detail id: {$item->id_shoe_detail}).");
                    $id_sucursal = Sucursal::where('name', $item->sucursal)->firstOrFail()->value('id');
                    $shoe_detail = ShoeDetail::findOrFail($item->id_shoe_detail);

                    Log::info(self::LOG_LABEL. " Updating sucursal item and shoe detail...");
                    
                    $sucursal_item = ShoeSucursalItem::where([
                        ['id_shoe_detail', $item->id_shoe_detail],
                        ['id_sucursal', $id_sucursal]])
                        ->first();

                    $sucursal_item->stock += $item->qty;
                    $shoe_detail->stock += $item->qty;

                    $sucursal_item->save();
                    $shoe_detail->save();

                    Log::info(self::LOG_LABEL. " Success updating sucursal item and shoe detail");

                    $item->delete();
                    Log::info(self::LOG_LABEL. " Item deleted success");
                }

                Log::info(self::LOG_LABEL. " Deleting tiendanube order...");
                
                $orderable = Order::findOrFail($order->id);
                $order->delete();

                Log::info(self::LOG_LABEL. " Deleting order...");

                $orderable->delete();

                Log::info(self::LOG_LABEL. " SUCCESS. Order has been deleted.");

                DB::commit();
            }
            catch (Exception $e) {
                Log::error(self::LOG_LABEL. " ERROR. There was an error");
                Log::error($e);
                DB::rollBack();
                $error = new TiendanubeError();
                $error->category = "order";
                $error->method = "cancelled";
                $error->id_tiendanube = $id_order;
                $error->save();
            }
        }
    }

    private function sendRequest($url, $retries = 3, $timeout = 10) {
        Log::info(self::LOG_LABEL." Sending request to get data: ".self::TIENDA_NUBE_API_URL.$url);
        $request = Http::withHeaders([
            'Authentication' => 'bearer '.\Config('tiendaNube.api_key'),
        ])
        ->withOptions([
            'synchronous' => true,
        ])
        ->retry($retries, 100)
        ->timeout($timeout);      
        
        $response = $request->get(self::TIENDA_NUBE_API_URL.$url);
            
        Log::info(self::LOG_LABEL." Response received");

        return $response;
    }

    public function borrar(Request $request) {
        $ids_brands = [1, 44, 5, 45, 46, 47, 69];
        $brand = 44;
        $request = Http::withHeaders([
            'Authentication' => 'bearer '.\Config('tiendaNube.api_key'),
        ])
        ->withOptions([
            'synchronous' => true,
        ])
        ->retry(3, 100)
        ->timeout(10);      

        $shoes = Shoe::select('shoes.id as id', 'shoe_brands.name as brand', 'shoes.code as code')
                ->join("shoe_brands", "shoe_brands.id", 'shoes.id_brand')
                ->whereIn('shoes.id', ShoeDetail::select('id_shoe')->where('id', '>', 89597)->get())
                #->where('shoes.id_brand', $brand)
                ->get();
        Log::info("Artículos: ".count($shoes));
        foreach ($shoes as $shoe) {
            $data = [];
            $colors = ShoeDetail::select('shoe_colors.name as color', 'shoe_colors.id as id')
                        ->join('shoe_colors', 'shoe_details.id_color', 'shoe_colors.id')
                        ->where('shoe_details.id_shoe', $shoe->id)
                        ->distinct()
                        ->get();

            $shoesDetails = ShoeDetail::select('shoe_colors.name as color', 'shoe_details.number as number', 'shoe_details.sell_price as price', 'shoe_details.id as sku', 'shoe_details.stock as stock' )
                    ->join('shoe_colors', 'shoe_details.id_color', 'shoe_colors.id')
                    ->where('shoe_details.id_shoe', $shoe->id)
                    ->where('shoe_details.id', '>', 88694)
                    ->whereNotIn('shoe_details.id', MappingTiendanube::select('id')->get())
                    ->get();

            foreach($colors as $color) {
                $data[$color->color] = ['name' => $shoe->brand.' '.$shoe->code, 
                'attributes' => [['es' => 'Talle'], ['es' => 'Color']],
                'published' => false,
                'variants' => []
                ];
            }
            foreach($shoesDetails as $detail){
                $new = ['price' => $detail->price,
                    'stock' => $detail->stock,
                    'weight' => '0.500',
                    'width' => '15.00',
                    'height' => '8.00',
                    'depth' => '25.00',
                    'sku' =>  $detail->sku,
                    'values' => [
                            ['es' => strval($detail->number)],
                            ['es' => $detail->color]
                        ]
                    ];
                $data[$detail->color]['variants'][] = $new;
            }
            #Log::info(self::LOG_LABEL." {$shoe->brand} {$shoe->code}");

            foreach($data as $d) {
                if (count($d['variants'])) {
                    #Log::info(self::LOG_LABEL." {$shoe->brand} {$shoe->code}: ". $d['variants'][0]['values'][1]['es']);
                    Log::info(self::LOG_LABEL." Sending requests color: ". $d['variants'][0]['values'][1]['es']);
                    $response = $request->post(self::TIENDA_NUBE_API_URL.'products', $d);
                }
            }
        }

    }

    public function map_tiendanube_products (Request $request) {
        Log::info(self::LOG_LABEL." New request to map tiendanube products");

        $mapped = 0;
        $errors = 0;
        $success = 0;
        $stop = false;
        $i = 0;

        $sku_null_list = [];
        $duplicated = [];
        $not_in_db = [];

        while(!$stop){
            $i += 1;
            try {
                $response = $this->sendRequest("products?page=".$i."&per_page=200&fields=variants,id");
                DB::beginTransaction();

                $total = count($response->json());
                foreach($response->json() as $product) {
                    foreach($product['variants'] as $item) {
                        try {
                            $id = $item['id'];
                            $sku = $item['sku'];
                            
                            if ($sku == null) {
                                if (!in_array($product['id'], $sku_null_list))
                                    $sku_null_list[] = $product['id'];
                                continue;
                            }
                            
                            // check if sku exists
                            $shoe_detail = ShoeDetail::findOrFail(intval($sku));

                            $is_mapped = MappingTiendanube::where('id_tiendanube', $id)->first();
                            if ($is_mapped) {
                                $mapped += 1;
                                continue;
                            }
                            
                            $mapping = new MappingTiendanube();
                            $mapping->id_tiendanube = $id;
                            $mapping->id_tiendanube_product = $product['id'];
                            $mapping->id_shoe_detail = $sku;
                            $mapping->id_shoe = $shoe_detail->id_shoe;
                            $mapping->id_tiendanube_store = self::TIENDANUBE_START_STORE_ID;
                            $mapping->save();
                            $success += 1;
                        }
                        catch(QueryException $e) {
                            $errors += 1;
                            $duplicated[] = $item['sku'];
                        }
                        catch(ModelNotFoundException $e) {
                            $errors += 1;
                            $not_in_db[] = $item['sku'];
                        }
                        catch(Exception $e) {
                            $errors += 1;
                            Log::error("Error para sku: {$item['sku']} - id: {$item['id']}");
                            Log::error($e);
                        }
                    }
                }
                DB::commit();
            }
            catch(RequestException $e) {
                $stop = true;
            }
            catch (Exception $e) {
                $stop = true;
                Log::info($e);
            }
        }
        Log::info(self::LOG_LABEL." Skus que no existen en start: ");
        Log::info($not_in_db);
        Log::info(self::LOG_LABEL." Skus duplicados en tiendanube: ");
        Log::info($duplicated);
        Log::info(self::LOG_LABEL." Skus nullos: ");
        Log::error($sku_null_list);
        Log::info(self::LOG_LABEL." Procces endend. {$success} product/s mapped and {$errors} errors. {$mapped} were already mapped.");
    }

    public function update_stock_tiendanube (Request $request) {

        Log::info(self::LOG_LABEL." New request to update stock in tiendanube");

        $id_store = self::TIENDANUBE_START_STORE_ID;
        $last_update = TiendanubeUpdateRun::where('action', 'update')->latest('created_at')->first();
        $failed = [];
        Log::info(self::LOG_LABEL." Updating mapping products...");
        
        $shoe_details = ShoeDetail::select('shoe_details.id as sku', 'shoe_details.stock', 'mapping_tiendanubes.id_tiendanube_product', 'mapping_tiendanubes.id_tiendanube', 'shoe_details.stock as stock', 'shoe_details.sell_price as price')
                    #->where('shoe_details.updated_at', '>', $last_update->created_at)
                    ->where('mapping_tiendanubes.id_tiendanube_store', $id_store)
                    ->where('shoe_details.id', '>', 88694)
                    ->join('mapping_tiendanubes', 'shoe_details.id', 'mapping_tiendanubes.id_shoe_detail')
                    ->get();
        
        Log::info(self::LOG_LABEL." Products to update: ".count($shoe_details));
        $i = 0;
        foreach ($shoe_details as $item) {
            try { 
                $i++;
                Log::info("Updating {$i} {$item->sku} $ {$item->price}, stock: {$item->stock}...");
                $request = $this->createRequest();
                $request->put(self::TIENDA_NUBE_API_URL.'products/'.$item->id_tiendanube_product.'/variants/'.$item->id_tiendanube,[
                    //'price' => $item->price,
                    //'promotional_price' => 0,
                    //TODO
                    'stock' => $item->stock
                ]);
            }
            catch (Exception $e) {
                Log::error(self::LOG_LABEL." ERROR. Error updating SKU { $item->sku }.");
                Log::error($e);
                $failed[] = $item->sku;
            }
            
        }
        Log::info(self::LOG_LABEL." Updating mapped product finished. Failed items: ");
        Log::info($failed);
        $new_run = new TiendanubeUpdateRun();
        $new_run->action = 'update';
        foreach($failed as $f) {
            $row = new TiendaNubeStockError();
            $row->action = "update";
            $row->id_shoe_detail = $item->sku;
            $row->save();
        }
        //TODO
        $new_run->save();
    }

    public function create_product_tiendanube (Request $request) {

        Log::info(self::LOG_LABEL." New request to create products in tiendanube");

        $id_store = self::TIENDANUBE_START_STORE_ID;
        $last_update = TiendanubeUpdateRun::where('action', 'create')->latest('created_at')->first();
        $new_products = [];
        $new_variants = [];

        try {
            Log::info(self::LOG_LABEL." Creating new products...");

            $shoe_details = ShoeDetail::select('shoe_brands.name as brand', 'shoes.code as code', 'shoe_colors.name as color', 'shoe_details.id_shoe as id_shoe', 'shoe_details.number as number', 'shoe_details.id as sku', 'shoe_details.stock', 'shoe_details.sell_price as price', 'shoe_details.id_color as id_color')
                        ->where('shoe_details.updated_at', '>', $last_update->created_at)
                        ->join('shoe_colors', 'shoe_details.id_color', 'shoe_colors.id')
                        ->join('shoes', 'shoe_details.id_shoe', 'shoes.id')
                        ->join('shoe_brands', 'shoes.id_brand', 'shoe_brands.id')
                        //TODO delete this
                        ->where('shoe_details.id', '>', 87798)
                        ->whereNotExists( function ($query) use ($id_store) {
                            $query->select(DB::raw(1))
                            ->from('mapping_tiendanubes')
                            ->whereRaw('shoe_details.id = mapping_tiendanubes.id_shoe_detail')
                            ->where('mapping_tiendanubes.id_tiendanube_store', $id_store);
                        })
                        ->get();

            Log::info(self::LOG_LABEL." Products to create: ".count($shoe_details));
            
            foreach($shoe_details as $detail){
                //Create Variant
                $new_variant = ['price' => $detail->price,
                        'stock' => $detail->stock,
                        'weight' => '0.500',
                        'width' => '15.00',
                        'height' => '8.00',
                        'depth' => '25.00',
                        'sku' =>  $detail->sku,
                        'values' => [
                                ['es' => strval($detail->number)],
                                ['es' => $detail->color]
                            ]
                        ];

                //check if exists shoe in TN
                $exists = MappingTiendanube::join('shoe_details', 'mapping_tiendanubes.id_shoe_detail', 'shoe_details.id')
                        ->where('shoe_details.id_color', $detail->id_color)
                        ->where('shoe_details.id_shoe', $detail->id_shoe)
                        ->first();
                
                if (!$exists) {
                    //Check si ya se procesó un variant 
                    if (!array_key_exists($detail->id_shoe . $detail->color, $new_products)) {
                        $new_product = ['name' => $detail->brand.' '.$detail->code, 
                        'attributes' => [['es' => 'Talle'], ['es' => 'Color']],
                        'published' => false,
                        'variants' => [$new_variant]
                        ];
                        $new_products[$detail->id_shoe . $detail->color] = $new_product;
                    }
                    else {
                        $new_products[$detail->id_shoe . $detail->color]['variants'][] = $new_variant;
                    }
                }
                else {
                    $new_variants[] = [
                        'id_tiendanube' => $exists->id_tiendanube_product,
                        'variant' => $new_variant
                    ];
                }
            }
           
            //Sending new products
            foreach($new_products as $new) {
                try {
                    $request = $this->createRequest();
                    Log::info(self::LOG_LABEL." Sending request to tiendanube");
                    $response = $request->post(self::TIENDA_NUBE_API_URL.'products', $new);
                    Log::info(self::LOG_LABEL. " Ok.");
                }
                catch (Exception $e) {
                    Log::error(self::LOG_LABEL. " ERROR. Could not perform create action for new product {$new['name']}.");
                }
            }

            //sending new variants
            foreach($new_variants as $new) {
                try {
                    $request = $this->createRequest();
                    Log::info(self::LOG_LABEL." Sending request to tiendanube: ".self::TIENDA_NUBE_API_URL."products/{$new['id_tiendanube']}/variants");
                    $response = $request->post(self::TIENDA_NUBE_API_URL."products/{$new['id_tiendanube']}/variants", $new['variant']);
                    Log::info(self::LOG_LABEL. " Ok.");
                }
                catch (Exception $e) {
                    Log::error($e);
                    Log::error(self::LOG_LABEL. " ERROR. Could not perform create action for new variant (id tiendanube: {$new['id_tiendanube']}).");
                }
            }
        }
        catch (Exception $e) {
            Log::error($e);
        }
    }

    private function createRequest($retries = 3, $timeout = 10) {
        $request = Http::withHeaders([
            'Authentication' => 'bearer '.\Config('tiendaNube.api_key'),
        ])
        ->withOptions([
            'synchronous' => true,
        ])
        ->retry($retries, 100)
        ->timeout($timeout);      
        
        return $request;
    }

}
