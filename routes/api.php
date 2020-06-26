<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\ShoeBrand;
use App\ShoeCategory;
use App\ShoeNumber;
use App\ShoeColor;
use App\ShoeDetail;
use App\ShoeSucursalItem;
use App\Shoe;
use App\Sucursal;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/get_brands',function(){
    $brands = ShoeBrand::get();
    return response()->json($brands);
});

Route::get('/get_colors',function(){
    $colors = ShoeColor::get();
    return response()->json($colors);
});

Route::get('/get_categories',function(){
    $categories = ShoeCategory::get();
    return response()->json($categories);
});

Route::get('/get_numbers',function(){
    $numbers = ShoeNumber::get();
    return response()->json($numbers);
});
/*
Route::get('/get_articles/{id_brand?}', function ($id_brand = null, Request $request) {
    $query = $request->input('query');
    $articles = Shoe::where('code','like','%'.$query.'%')->get();
    return response()->json($articles);
});
*/
Route::get('/get_articles',function(Request $request){
    $query = $request->input('query');
    $articles = Shoe::where('code','like','%'.$query.'%')
                    ->join('shoe_brands', 'shoes.id_brand', '=', 'shoe_brands.id')
                    ->select('shoes.*', 'shoe_brands.name as brand_name')
                    ->limit(100)->get();
    return response()->json($articles);
});

Route::get('/get_articles/{id}',function($id, Request $request){
    $query = $request->input('query');
    $articles = Shoe::where([
                            ['id_brand', $id],
                            ['code','like','%'.$query.'%']])
                    ->join('shoe_brands', 'shoes.id_brand', '=', 'shoe_brands.id')
                    ->select('shoes.*', 'shoe_brands.name as brand_name')
                    ->limit(100)->get();
    return response()->json($articles);
});

Route::get('/get_detail_item',function(Request $request){
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
    Log::info('Returned '.$detail);

    return response()->json($detail);
});

Route::post('/new_brand',function(Request $request){
    Log::info("New request to create brand received: ".$request->getContent()); 
    $name = $request->input('name');
    try{
        $new = new ShoeBrand();
        $new->name = $name; 
        $new->save();
        $statusCode = 200;
        $message = "La marca $name se agregó con éxito.";
    }
    catch(Exception $e){
        $statusCode = 501;
        $message = "Hubo un error al guardar $name en la base de datos.";
        Log::error($e);
    }
    return response()->json(['data' => $new, 'message'=> $message, 'statusCode' => $statusCode ]);
});

Route::post('/new_article',function(Request $request){
    Log::info("New request to create article received: ".$request->getContent()); 
    $code = $request->input('code');
    $id_brand = $request->input('id_brand');
    try{
        $new = new Shoe();
        $new->code = $code;
        $new->id_brand = $id_brand;
        $new->save();
        $statusCode = 200;
        $message = "El artículo $code se agregó con éxito.";
    }
    catch(Exception $e){
        $statusCode = 501;
        $message = "Hubo un error al guardar $code en la base de datos.";
        Log::error($e);
    }
    return response()->json(['data' => $new, 'message'=> $message, 'statusCode' => $statusCode ]);
});

Route::post('/new_color',function(Request $request){
    Log::info("New request to create color received: ".$request->getContent()); 
    $name = $request->input('name');
    try{
        $new = new ShoeColor();
        $new->name = $name;
        $new->save();
        $statusCode = 200;
        $message = "El color $name se agregó con éxito.";
    }
    catch(Exception $e){
        $statusCode = 501;
        $message = "Hubo un error al guardar $name en la base de datos.";
        Log::error($e);
    }
    return response()->json(['data' => $new, 'message'=> $message, 'statusCode' => $statusCode ]);
});

Route::post('/update_items',function(Request $request){
    Log::info("Request to create/update stock items received: ".$request->getContent()); 

    $status = '¡Bien papá!';
    $message = 'El stock se actualizó correctamente';
    $statusCode = 200;

    $items = $request->input('items');
    $return_back_items = [];

    foreach ($items as $item) {
        //TODO AGREGAR EN STOCK ITEM SUCURSAL!!!
        //NEW
        try {
            if ( !array_key_exists('barcode', $item) ){
                Log::info("New item to add ".json_encode($item)); 
                Log::info("Start insert to database"); 
                $record  = ShoeDetail::create($item);
                $record->barcode = str_pad(ShoeDetail::max('id'), 12, '0', STR_PAD_LEFT);
                $record->save();
                Log::info("Success. Finished insert to database id:".$record->id); 
            }
            //UPDATE
            else {
                $record = ShoeDetail::find($item['id']);
                Log::info("Start update id: $record->id ".json_encode($item));
                $record->stock += $item['stock'];
                $record->sell_price = $item['sell_price'];
                $record->buy_price = $item['buy_price'];
                $record->available_tiendanube = $item['available_tiendanube'];
                $record->available_marketplace = $item['available_marketplace'];
                $record->save();
            }
            $return_back_items[] = $record;
            Log::info("Start updating main Sucursal Stock to database"); 
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
            Log::info("Finished update main Sucursal Stock"); 
        }
        catch (Exception $e) {
            Log::error('There was an error with: '.json_encode($item));
            Log::error($e);
            $status = "Ups. Algo salió mal";
            $message = "Hubo un error con algún número. Recargar la página y chequear si el stock se actualizó";
            $statusCode = 501;
        }
    }
    return response()->json(["items" => $return_back_items, "status" => $status, "message" => $message, 'statusCode' => $statusCode] );

});