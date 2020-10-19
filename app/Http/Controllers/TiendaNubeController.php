<?php

namespace App\Http\Controllers;

use Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

use App\ShoeDetail;
use App\ShoeSucursalItem;
use App\Sucursal;
use App\OrderTiendanube;
use App\OrderItem;
use App\Order;
use App\TiendanubeError;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TiendaNubeController extends Controller
{
    const LOG_LABEL = "[TIENDANUBE API]";
    const TIENDA_NUBE_API_URL = "https://api.tiendanube.com/v1/1153537/";
    const TIENDA_NUBE_USER_ID = 9;
    const ORDER_STATUS = ['created' => 0, 'fulfilled' => 1, 'cancelled' => 2];

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

    public function sendRequest($url) {
        Log::info(self::LOG_LABEL." Sending request to get data");
        $request = Http::withHeaders([
            'Authentication' => 'bearer '.\Config('tiendaNube.api_key'),
        ])
        ->withOptions([
            'synchronous' => true,
        ])
        ->retry(3, 100)
        ->timeout(3);      

        $response = $request->get(self::TIENDA_NUBE_API_URL.$url);
        Log::info(self::LOG_LABEL." Response received");

        return $response;
    }

    public function update_stock_tiendanube (Request $request) {
        // hacer algo en la base de shoe detail para detectar si ya existe en tn
        // o hacer una tabla relacionando tiendanube / start Analizar

        $response = $this->sendRequest("products");

        Log::info($response);

    }

}
