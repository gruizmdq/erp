<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

use Log;
use App\ShoeBrand;
use App\ShoeCategory;
use App\ShoeNumber;
use App\ShoeColor;
use App\ShoeDetail;
use App\ShoeSucursalItem;
use App\Shoe;
use App\Sucursal;

use Exception;
use App\Exceptions\Stock\ExistsSucursalItemException;


class StockController extends Controller{

    const LOG_LABEL = '[STOCK API]';
    const STATUS_ERROR_TITLE = "Ups. Algo salió mal";
    const STATUS_SUCCESS_TITLE = "¡Bien papá!";
    
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function get_brands (Request $request) {
        $request->user()->authorizeRoles(['admin', 'seller']);
        $brands = ShoeBrand::get();
        return response()->json($brands);
    }

    public function get_colors (Request $request) {
        $request->user()->authorizeRoles(['admin', 'seller']);

        $id_shoe = $request->input('id_article', null);
        if ($id_shoe) {
            $shoe = Shoe::findOrFail($id_shoe);
            $colors = $shoe->getColors();
        }
        else
            $colors = ShoeColor::get();

        return response()->json($colors);
    }

    public function get_numbers (Request $request) {
        $request->user()->authorizeRoles(['admin', 'seller']);

        $id_shoe = $request->input('id_article', null);
        $id_color = $request->input('id_color', null);
        Log::info($id_shoe);
        Log::info($id_color);
        $shoe = Shoe::findOrFail($id_shoe);
        $numbers = $shoe->getNumbers($id_color);

        return response()->json($numbers);
    }

    public function get_categories (Request $request) {
        $request->user()->authorizeRoles(['admin']);
        $categories = ShoeCategory::get();
        return response()->json($categories);
    }

    public function get_list_stock(Request $request) {
        $request->user()->authorizeRoles(['admin']);
        
        $id_brand = $request->input('id_brand');
        $shoe_details = ShoeDetail::where('shoes.id_brand', $id_brand)
                                    ->join('shoes', 'shoes.id', '=', 'shoe_details.id_shoe')
                                    ->join('shoe_colors', 'shoe_colors.id', '=', 'shoe_details.id_color')
                                    ->join('shoe_brands', 'shoes.id_brand', '=', 'shoe_brands.id')
                                    ->select('shoe_brands.name as brand_name', 'shoe_details.*', 'shoes.code as code', 'shoe_colors.name as color')
                                    ->orderBy('code')
                                    ->orderBy('color')
                                    ->orderBy('number')
                                    ->get();
        Log::info("Count for id_brand $id_brand: ".sizeof($shoe_details));
        foreach ($shoe_details as $shoe){
            $stock_sucursal_items = ShoeSucursalItem::where('id_shoe_detail', $shoe->id)
                                                    ->join('sucursals', 'shoe_sucursal_items.id_sucursal', '=', 'sucursals.id')
                                                    ->select('sucursals.id as id_sucursal', 'shoe_sucursal_items.stock as stock')
                                                    ->get();
            $shoe->items = $stock_sucursal_items;
        }
        return response()->json($shoe_details);
    }

    public function get_articles (Request $request) {
        $request->user()->authorizeRoles(['admin', 'seller', 'cashier']);

        $query = $request->input('query');
        $articles = Shoe::where('code','like','%'.$query.'%')
                        ->join('shoe_brands', 'shoes.id_brand', '=', 'shoe_brands.id')
                        ->select('shoes.*', 'shoe_brands.name as brand_name')
                        ->limit(100)->get();
        return response()->json($articles);
    }

    public function get_articles_id (Request $request) {
        $request->user()->authorizeRoles(['admin']);

        $id = Route::current()->parameter('id');
        $query = $request->input('query');
        $articles = Shoe::where([
                                ['id_brand', $id],
                                ['code','like','%'.$query.'%']])
                        ->join('shoe_brands', 'shoes.id_brand', '=', 'shoe_brands.id')
                        ->select('shoes.*', 'shoe_brands.name as brand_name')
                        ->limit(100)->get();
        return response()->json($articles);
    }

    public function get_article_items (Request $request) {
        $request->user()->authorizeRoles(['admin']);
        
        //TODO agregar validates
        $id_shoe = $request->input('id_shoe');
        try {
            $shoe = Shoe::findOrFail($id_shoe);
            $items = $shoe->getItems();
        }
        catch (ModelNotFoundException $e) {
            Log::error($e);
            return response()->json(["item" => $detail, "title" => self::STATUS_ERROR_TITLE, "status" => 'error', "statusCode" => 501, "message" => 'No se encontró el artículo en la base de datos']);
        }
        catch (Exception $e) {
            Log::error($e);
            return response()->json(["item" => $detail, "title" => self::STATUS_ERROR_TITLE, "status" => 'error', "statusCode" => 501, "message" => 'Hubo un error inesperado.']);
        }
        return $items;
    }

    public function update_article_items (Request $request) {
        $request->user()->authorizeRoles(['admin']);

        Log::info("*****************************************");
        Log::info(self::LOG_LABEL." New request to update article items received: ".$request->getContent());
        
        $values = $request->input('values');
        $id_shoe = $request->input('id_shoe');

        $statusCode = 200;
        $message = 'Se actualizaron los artículos con éxito';
        $status = self::STATUS_SUCCESS_TITLE;

        try {
            ShoeDetail::where([
                ['id_shoe', $id_shoe],
                ['number', '>=', $values['from']],
                ['number', '<=', $values['to']],
            ])->update([
                'buy_price' => $values['buy_price'],
                'sell_price' => $values['sell_price'],
                'available_tiendanube' => $values['available_tiendanube'],
                'available_marketplace' => $values['available_marketplace']
            ]);
            
            Log::info(self::LOG_LABEL." SUCCESS. Update article items request success.");
        }
        catch(Exception $e){
            $statusCode = 501;
            $message = "Hubo un error en la base de datos.";
            $status = self::STATUS_ERROR_TITLE;
            Log::error($e);
        }
            return response()->json(['status' => $status, 'message'=> $message, 'statusCode' => $statusCode ]);
    }

    public function get_single_detail_item (Request $request) {
        $request->user()->authorizeRoles(['admin']);
        
        $id_shoe = $request->input('id_shoe');
        $id_color = $request->input('id_color');
        $number = $request->input('number');

        return ShoeDetail::where([
            ['id_shoe', $id_shoe],
            ['id_color', $id_color],
            ['number', $number]
        ])->first();
    }

    public function get_detail_item (Request $request){
        $request->user()->authorizeRoles(['admin']);

        $id_shoe = $request->input('id_shoe');
        $id_color = $request->input('id_color');
        $get_sucursal_items = $request->input('get_sucursal_items');

        $to = $request->input('to');
        $from = $request->input('from');
        $detail = ShoeDetail::where([
                                ['id_shoe', $id_shoe],
                                ['id_color',$id_color],
                                ['number', '<=', $to],
                                ['number', '>=', $from]])
                                ->orderByRaw('number ASC')
                                ->get();

        if($get_sucursal_items) {
            foreach($detail as $d)
                $d->shoeSucursalItem;
        }
        
        Log::info(self::LOG_LABEL.' Returned '.$detail);

        return response()->json($detail);
    }

    public function get_detail_item_barcode (Request $request){
        $request->user()->authorizeRoles(['admin', 'seller', 'cashier']);

        $barcode = (int)($request->input('barcode'));
        $detail = [];
        try {
            $detail = ShoeDetail::findOrFail($barcode);
            $detail->shoeSucursalItem;
            $shoe = Shoe::find($detail->id_shoe);
            $detail->code = $shoe->code;
            $detail->brand_name = ShoeBrand::where('id', $shoe->id_brand)->value('name');
            $detail->color = ShoeColor::where('id', $detail->id_color)->value('name');
            return response()->json(["item" => $detail, "title" => self::STATUS_SUCCESS_TITLE, "status" => 'success', "statusCode" => 200]);
        }
        catch (ModelNotFoundException $e) {
            return response()->json(["item" => $detail, "title" => self::STATUS_ERROR_TITLE, "status" => 'error', "statusCode" => 501, "message" => 'No se encontró el artículo en la base de datos']);
        }
        catch (Exception $e) {
            return response()->json(["item" => $detail, "title" => self::STATUS_ERROR_TITLE, "status" => 'error', "statusCode" => 501, "message" => 'Hubo un error. Chupala.']);
        }
    }

    public function new_brand (Request $request) {
        $request->user()->authorizeRoles(['admin']);

        Log::info("*****************************************");
        Log::info(self::LOG_LABEL." New request to create brand received: ".$request->getContent()); 
        $name = $request->input('name');
        try{
            $new = new ShoeBrand();
            $new->name = $name; 
            $new->save();
            $statusCode = 200;
            $message = "La marca $name se agregó con éxito.";
            Log::info(self::LOG_LABEL." Success. Request for $name completed");
        }
        catch(Exception $e){
            $statusCode = 501;
            $message = "Hubo un error al guardar $name en la base de datos.";
            Log::error($e);
        }
        return response()->json(['data' => $new, 'message'=> $message, 'statusCode' => $statusCode ]);
    }

    public function new_article (Request $request) {
        $request->user()->authorizeRoles(['admin']);

        Log::info("*****************************************");
        Log::info(self::LOG_LABEL." New request to create article received: ".$request->getContent()); 
        $code = $request->input('code');
        $id_brand = $request->input('id_brand');
        try{
            $new = new Shoe();
            $new->code = $code;
            $new->id_brand = $id_brand;
            $new->save();
            $statusCode = 200;
            $message = "El artículo $code se agregó con éxito.";
            Log::info(self::LOG_LABEL." Success. Request for $code completed");
        }
        catch(Exception $e){
            $statusCode = 501;
            $message = "Hubo un error al guardar $code en la base de datos.";
            Log::error(self::LOG_LABEL.' '.$e);
        }
        return response()->json(['data' => $new, 'message'=> $message, 'statusCode' => $statusCode ]);
    }

    public function new_color (Request $request) {
        $request->user()->authorizeRoles(['admin']);

        Log::info("*****************************************");
        Log::info(self::LOG_LABEL." New request to create color received: ".$request->getContent()); 
        $name = $request->input('name');
        try{
            $new = new ShoeColor();
            $new->name = $name;
            $new->save();
            $statusCode = 200;
            $message = "El color $name se agregó con éxito.";
            Log::info(self::LOG_LABEL." Success. Request for $name completed");
        }
        catch(Exception $e){
            $statusCode = 501;
            $message = "Hubo un error al guardar $name en la base de datos.";
            Log::error(self::LOG_LABEL.' '.$e);
        }
        return response()->json(['data' => $new, 'message'=> $message, 'statusCode' => $statusCode ]);
    }

    public function update_items (Request $request) {
        $request->user()->authorizeRoles(['admin']);

        Log::info("*****************************************");
        Log::info(self::LOG_LABEL." Request to create/update stock items received: ".$request->getContent()); 

        $status = self::STATUS_SUCCESS_TITLE;
        $message = 'El stock se actualizó correctamente';
        $statusCode = 200;

        $items = $request->input('items');
        $color = $request->input('color');
        $brand_name = $request->input('brand_name');
        $return_back_items = [];

        DB::beginTransaction();

        foreach ($items as $item) {
            if ($item['stock_to_add'] > 0)
            try {
                #TODO cambiar esto para que agarre barcode del ultimo
                if ( !array_key_exists('barcode', $item) ){
                    Log::info(self::LOG_LABEL." New item to add ".json_encode($item)); 
                    Log::info(self::LOG_LABEL." Start insert to database"); 

                    $record  = ShoeDetail::create($item);
                    $record->barcode = str_pad(ShoeDetail::max('id'), 12, '0', STR_PAD_LEFT);
                    Log::info($record);
                }
                //UPDATE
                else {
                    $record = ShoeDetail::find($item['id']);
                    Log::info(self::LOG_LABEL." Start update for id: $record->id ".json_encode($item));
                }

                $record->stock += $item['stock_to_add'];
                $record->sell_price = $item['sell_price'];
                $record->buy_price = $item['buy_price'];
                $record->available_tiendanube = $item['available_tiendanube'];
                $record->available_marketplace = $item['available_marketplace'];
                $record->save();
                
                //TODO: Esto es para que el front vuelva a captar la info... Ver mejor opción
                $record->stock_to_add = $item['stock_to_add'];
                Log::info(self::LOG_LABEL." Success. Request for $record->id completed");
                
                $return_back_items[] = $record;

                Log::info(self::LOG_LABEL." Start updating main Sucursal Stock to database"); 
                $sucursal_item = ShoeSucursalItem::where([
                                ['id_shoe_detail', $record->id],
                                ['id_sucursal', 1]])
                                ->first();

                if ($sucursal_item == null) {
                    $sucursal_item = new ShoeSucursalItem();
                    $sucursal_item->stock = $item['stock_to_add'];
                    $sucursal_item->id_shoe_detail = $record->id;
                    $sucursal_item->id_sucursal = 1;

                    //TODO: Hacer dinámico esto, es una verga así
                    $sucursal_rufina = new ShoeSucursalItem();
                    $sucursal_rufina->stock = 0;
                    $sucursal_rufina->id_shoe_detail = $record->id;
                    $sucursal_rufina->id_sucursal = 2;
                    $sucursal_rufina->save();
                }
                else {
                    $sucursal_item->stock += $item['stock_to_add'];  
                }
                $sucursal_item->save();
                Log::info(self::LOG_LABEL." Success. Finished update main Sucursal Stock");
            }
            catch (Exception $e) {
                Log::error(self::LOG_LABEL.' ERROR. There was an error with: '.json_encode($item));
                Log::error($e);
                $status = self::STATUS_ERROR_TITLE;
                $message = "Hubo un error con algún número. Recargar la página y chequear si el stock se actualizó";
                $statusCode = 501;
            }            
        }
        if ($statusCode == 501) {
            DB::rollBack();
        }
        else {
            DB::commit();
        }
        return response()->json(["items" => $return_back_items, "status" => $status, "message" => $message, 'statusCode' => $statusCode] );
    }

    public function delete_items (Request $request) {
        $request->user()->authorizeRoles(['admin']);

        Log::info("*****************************************");
        Log::info(self::LOG_LABEL." Request to delete items received: ".json_encode($request->getContent()));
        
        $items = $request->input('items', null);
        $failed = [];
        $success = [];
        $status = self::STATUS_SUCCESS_TITLE;
        $message = 'El stock se modificó correctamente';
        $statusCode = 200;

        if ($items != null) {
            foreach ($items as $id) {
                Log::info(self::LOG_LABEL." Start delete proccess for $id.");
                try {
                    $row = ShoeDetail::findOrFail($id);
                    //Removing sucursal item.
                    $childs = $row->shoeSucursalItem;
                    foreach ($childs as $i)
                        $i->delete();
                    $row->delete();                   

                    $success[] = $id;
                    Log::info(self::LOG_LABEL."Success. Delete success for $id.");
                }
                catch (ModelNotFoundException $e) {
                    Log::error(self::LOG_LABEL."ERROR. Item with $id not found.");
                    $failed[] = $id;
                }
                catch (Exception $e) {
                    Log::error(self::LOG_LABEL."ERROR. There was an error with id: $id");
                    Log::error($e);
                    $failed[] = $id;
                }
            }
            if (count($failed)) {
                $message = 'Hubo problemas con algunos artículos.';
                $statusCode = 501;
            }
        }
        
        Log::info(self::LOG_LABEL."Proccess completed.". PHP_EOL ."Success: ".json_encode($success). PHP_EOL ."Failed: ".json_encode($failed));
        return response()->json(["success" => $success, "failed" => $failed,  "status" => $status, "message" => $message, 'statusCode' => $statusCode]);
    }

    public function delete_colors (Request $request) {
        $request->user()->authorizeRoles(['admin']);

        Log::info("*****************************************");
        Log::info(self::LOG_LABEL." Request to delete colors received: ".json_encode($request->getContent()));
        
        $items = $request->input('items', null);
        $failed = [];
        $success = [];
        $status = self::STATUS_SUCCESS_TITLE;
        $message = 'El stock se modificó correctamente';
        $statusCode = 200;

        if ($items != null) {
            foreach ($items as $id) {
                Log::info(self::LOG_LABEL." Start delete proccess for $id.");
                try {
                    DB::beginTransaction();

                    $row = ShoeColor::findOrFail($id);
                    $row->delete();

                    DB::commit();

                    $success[] = $id;
                    Log::info(self::LOG_LABEL."Success. Delete success for $id.");
                }
                catch (ModelNotFoundException $e) {
                    Log::error(self::LOG_LABEL."ERROR. Item with $id not found.");
                    $failed[] = $id;
                    DB::rollBack();
                }
                catch (ExistsSucursalItemException $e) {
                    Log::error(self::LOG_LABEL." ERROR. There are stock for id $id.");
                    $failed[] = $id;
                    DB::rollBack();
                }
                catch (Exception $e) {
                    Log::error(self::LOG_LABEL."ERROR. There was an error with id: $id");
                    Log::error($e);
                    $failed[] = $id;
                    DB::rollBack();
                }
            }
            if (count($failed)) {
                $message = 'Hubo problemas con algunos colores.';
                $statusCode = 501;
                $status = self::STATUS_ERROR_TITLE;
            }
        }

        Log::info(self::LOG_LABEL."Proccess completed.". PHP_EOL ."Success: ".json_encode($success). PHP_EOL ."Failed: ".json_encode($failed));
        return response()->json(["success" => $success, "failed" => $failed,  "status" => $status, "message" => $message, 'statusCode' => $statusCode]);
    }

    public function delete_brands (Request $request) {
        $request->user()->authorizeRoles(['admin']);

        Log::info("*****************************************");
        Log::info(self::LOG_LABEL." Request to delete brands received: ".json_encode($request->getContent()));
        
        $items = $request->input('items', null);
        $failed = [];
        $success = [];
        $status = self::STATUS_SUCCESS_TITLE;
        $message = 'El stock se modificó correctamente';
        $statusCode = 200;

        if ($items != null) {
            foreach ($items as $id) {
                Log::info(self::LOG_LABEL." Start delete proccess for $id.");
                try {
                    DB::beginTransaction();

                    $row = ShoeBrand::findOrFail($id);
                    //$articles = Shoe::where('id_brand', $row->id)->get();
                    
                    $row->delete();
                    $success[] = $id;
                    Log::info(self::LOG_LABEL." Success. Delete success for $id.");
                    
                    DB::commit();
                }
                catch (ModelNotFoundException $e) {
                    Log::error(self::LOG_LABEL." ERROR. Item with $id not found.");
                    $failed[] = $id;
                    DB::rollBack();
                }
                catch (ExistsCurusalItemException $e) {
                    Log::error(self::LOG_LABEL." ERROR. There are stock for id $id.");
                    $failed[] = $id;
                    DB::rollBack();
                }
                catch (Exception $e) {
                    Log::error(self::LOG_LABEL." ERROR. There was an error with id: $id");
                    Log::error($e);
                    $failed[] = $id;
                    DB::rollBack();
                }
            }
            if (count($failed)) {
                $message = 'Ups. Hubo algunos problemas.';
                $status = self::STATUS_ERROR_TITLE;
                $statusCode = 501;
            }
        }

        Log::info(self::LOG_LABEL."Proccess completed.". PHP_EOL ."Success: ".json_encode($success). PHP_EOL ."Failed: ".json_encode($failed));
        return response()->json(["success" => $success, "failed" => $failed,  "status" => $status, "message" => $message, 'statusCode' => $statusCode]);
    }

    public function edit_color (Request $request) {
        $request->user()->authorizeRoles(['admin']);

        Log::info("*****************************************");
        Log::info(self::LOG_LABEL." Request to edit colors received: ".json_encode($request->getContent()));
        
        $item = $request->input('item', null);
        $statusCode = 200;
        $status = self::STATUS_SUCCESS_TITLE;
        $message = "¡El color se editó correctamente!";

        if ($item != null) {
            try {
                $color = ShoeColor::findOrFail($item['id']);
                $color->name = $item['name'];
                $color->save();
                Log::info(self::LOG_LABEL." Success. Update success for $color.");
            }
            catch (ModelNotFoundException $e) {
                Log::error(self::LOG_LABEL." ERROR. Item with id {$item['id']} not found.");
                $status = self::STATUS_ERROR_TITLE;
                $message = "Ups. Hubo un error al buscar el color en la base de datos";
            }
            catch (Exception $e) {
                Log::error(self::LOG_LABEL." ERROR. There was an error with id: {$item['id']}");
                Log::error($e);
                $status = self::STATUS_ERROR_TITLE;
                $message = "Ups. Hubo un error";
            }
        }

        Log::info(self::LOG_LABEL." Proccess completed.");
        return response()->json(["status" => $status, "message" => $message, 'statusCode' => $statusCode]);
    }

    public function edit_brand (Request $request) {
        $request->user()->authorizeRoles(['admin']);

        Log::info("*****************************************");
        Log::info(self::LOG_LABEL." Request to edit brand received: ".json_encode($request->getContent()));
        
        $item = $request->input('item', null);
        $statusCode = 200;
        $status = self::STATUS_SUCCESS_TITLE;
        $message = "¡La marca se editó correctamente!";

        if ($item != null) {
            try {
                $brand = ShoeBrand::findOrFail($item['id']);
                $brand->name = $item['name'];
                $brand->save();
                Log::info(self::LOG_LABEL." Success. Update success for $brand.");
            }
            catch (ModelNotFoundException $e) {
                Log::error(self::LOG_LABEL." ERROR. Item with id {$item['id']} not found.");
                $status = self::STATUS_ERROR_TITLE;
                $message = "Ups. Hubo un error al buscar la marca en la base de datos";
            }
            catch (Exception $e) {
                Log::error(self::LOG_LABEL." ERROR. There was an error with id: {$item['id']}");
                Log::error($e);
                $status = self::STATUS_ERROR_TITLE;
                $message = "Ups. Hubo un error";
            }
        }

        Log::info(self::LOG_LABEL." Proccess completed.");
        return response()->json(["status" => $status, "message" => $message, 'statusCode' => $statusCode]);
    }

    
}
