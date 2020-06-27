<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use Log;
use App\ShoeBrand;
use App\ShoeCategory;
use App\ShoeNumber;
use App\ShoeColor;
use App\ShoeDetail;
use App\ShoeSucursalItem;
use App\Shoe;
use App\Sucursal;

class StockController extends Controller{

    const LOG_LABEL = '[STOCK API]';
    const STATUS_ERROR_TITLE = "Ups. Algo salió mal";
    const STATUS_SUCCESS_TITLE = "¡Bien papá!";
    
    public function index(Request $request) {
        return view('stock.home');
    }
    public function get_brands (Request $request) {
        $brands = ShoeBrand::get();
        return response()->json($brands);
    }

    public function get_colors () {
        $colors = ShoeColor::get();
        return response()->json($colors);
    }

    public function get_categories () {
        $categories = ShoeCategory::get();
        return response()->json($categories);
    }

    public function get_numbers(){
        $numbers = ShoeNumber::get();
        return response()->json($numbers);
    }

    public function get_list_stock(Request $request) {
        Log::info(json_encode($request->getContent()));
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
        $query = $request->input('query');
        $articles = Shoe::where('code','like','%'.$query.'%')
                        ->join('shoe_brands', 'shoes.id_brand', '=', 'shoe_brands.id')
                        ->select('shoes.*', 'shoe_brands.name as brand_name')
                        ->limit(100)->get();
        return response()->json($articles);
    }

    public function get_articles_id (Request $request) {
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

    public function get_detail_item (Request $request){
        $id_shoe = $request->input('id_shoe');
        $id_color = $request->input('id_color');
        $to = $request->input('to');
        $from = $request->input('from');
        $detail = ShoeDetail::where([
                                ['id_shoe', $id_shoe],
                                ['id_color',$id_color],
                                ['number', '<=', $to],
                                ['number', '>=', $from]])
                                ->orderByRaw('number ASC')
                                ->get();
        Log::info(self::LOG_LABEL.' Returned '.$detail);

        return response()->json($detail);
    }

    public function new_brand (Request $request) {
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
        Log::info(self::LOG_LABEL." Request to create/update stock items received: ".$request->getContent()); 

        $status = self::STATUS_SUCCESS_TITLE;
        $message = 'El stock se actualizó correctamente';
        $statusCode = 200;

        $items = $request->input('items');
        $return_back_items = [];

        foreach ($items as $item) {
            try {
                if ( !array_key_exists('barcode', $item) ){
                    Log::info(self::LOG_LABEL." New item to add ".json_encode($item)); 
                    Log::info(self::LOG_LABEL." Start insert to database"); 
                    $record  = ShoeDetail::create($item);
                    $record->barcode = str_pad(ShoeDetail::max('id'), 12, '0', STR_PAD_LEFT);
                    $record->save();
                    Log::info(self::LOG_LABEL." Success. Request for $record->id completed");
                }
                //UPDATE
                else {
                    $record = ShoeDetail::find($item['id']);
                    Log::info(self::LOG_LABEL." Start update for id: $record->id ".json_encode($item));
                    $record->stock += $item['stock'];
                    $record->sell_price = $item['sell_price'];
                    $record->buy_price = $item['buy_price'];
                    $record->available_tiendanube = $item['available_tiendanube'];
                    $record->available_marketplace = $item['available_marketplace'];
                    $record->save();
                }
                $return_back_items[] = $record;
                Log::info(self::LOG_LABEL." Start updating main Sucursal Stock to database"); 
                $sucursal_item = ShoeSucursalItem::where([
                                ['id_shoe_detail', $record->id],
                                ['id_sucursal', 1]])
                                ->first();
                if ($sucursal_item == null) {
                    $sucursal_item = new ShoeSucursalItem();
                    $sucursal_item->stock = $item['stock'];
                    $sucursal_item->id_shoe_detail = $record->id;
                    $sucursal_item->id_sucursal = 1;
                }
                else {
                    $sucursal_item->stock += $item['stock'];  
                }
                $sucursal_item->save();
                Log::info(self::LOG_LABEL."Success. Finished update main Sucursal Stock"); 
            }
            catch (Exception $e) {
                Log::error(self::LOG_LABEL.'ERROR. There was an error with: '.json_encode($item));
                Log::error($e);
                $status = self::STATUS_ERROR_TITLE;
                $message = "Hubo un error con algún número. Recargar la página y chequear si el stock se actualizó";
                $statusCode = 501;
            }
        }
        return response()->json(["items" => $return_back_items, "status" => $status, "message" => $message, 'statusCode' => $statusCode] );
    }

    public function delete_items (Request $request) {
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
}
