<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

use Exception;
use Log;

use App\Sucursals;
use App\StockReset;
use App\StockResetItem;
use App\ShoeDetail;
use App\ShoeSucursalItem;

class StockResetController extends Controller
{
    const LOG_LABEL = '[STOCK RESET API]';
    const STATUS_ERROR_TITLE = "Ups. Algo salió mal";
    const STATUS_SUCCESS_TITLE = "¡Bien papá!";

    public function get_stock_info(Request $request) {
        $request->user()->authorizeRoles(['admin']);

        Log::info("*****************************************");
        Log::info(self::LOG_LABEL." New request to retrieve stock info");

        $sucursal = $request->input('sucursal');
        $brand = $request->input('brand', null);

        try {
            $query = ShoeSucursalItem::select(DB::raw('sum(shoe_sucursal_items.stock) as total'))
                    ->where('id_sucursal', $sucursal);
            if ($brand)
                $query = $query->join('shoe_details', 'shoe_sucursal_items.id_shoe_detail', 'shoe_details.id')
                            ->join('shoes', 'shoe_details.id_shoe', 'shoes.id')
                            ->where('shoes.id_brand', $brand);
            
            $total = $query->first()->total;
        }
        catch (ModelNotFoundException $e) {
            Log::info($e);
            $total = 0;
        }

        return $total;
    }

    public function get_shoe_detail(Request $request) {
        $request->user()->authorizeRoles(['admin']);

        $sucursal = $request->input('sucursal');
        $brand = $request->input('brand', null);
        $barcode = $request->input('barcode');

        try {
            $query = ShoeDetail::select("shoe_details.*", 'shoes.code as code', 'shoe_colors.name as color', 'shoe_brands.name as brand')
                    ->join("shoe_sucursal_items", 'shoe_sucursal_items.id_shoe_detail', 'shoe_details.id')
                    ->join('shoes', 'shoe_details.id_shoe', 'shoes.id')
                    ->join('shoe_colors', 'shoe_details.id_color', 'shoe_colors.id')
                    ->join('shoe_brands', 'shoes.id_brand', 'shoe_brands.id')
                    ->where('shoe_details.id', (int) $barcode)
                    ->where('shoe_sucursal_items.id_sucursal', $sucursal);
            if ($brand)
                $query = $query->where('shoes.id_brand', $brand);
            
                            
            $detail = $query->first();
        }
        catch (Exception $e) {
            Log::info($e);
            $detail = null;
        }

        return $detail;
    }

    public function get_preview(Request $request) {
        $request->user()->authorizeRoles(['admin']);

        Log::info("*****************************************");
        Log::info(self::LOG_LABEL." New request to retrieve preview stock info");

        $sucursal = $request->input('sucursal');
        $brand = $request->input('brand', null);
        $items = $request->input('items');
        Log::info($items);
        $success_items = [];
        $error_items = [];
        $not_processed = [];

        foreach($items as $i) {
            $item = json_decode($i);

            try {
                Log::info(self::LOG_LABEL." Checking items for shoe detail item id: ".$item->data->id);

                $detail = ShoeSucursalItem::where('id_sucursal', $sucursal)
                            ->where('id_shoe_detail', $item->data->id)
                            ->first();
                
                if ($detail->stock == $item->qty) {
                    Log::info(self::LOG_LABEL." Success for shoe detail item id: ".$item->data->id);
                    $success_items[] = $item->data->id;
                }
                else {
                    Log::info(self::LOG_LABEL." Error for shoe detail item id: ".$item->data->id.". Diference: ".($detail->stock - $item->qty));
                    $error_items[] = ['id' => $item->data->id, 'diff' => ($detail->stock - $item->qty)];
                }
            }
            catch (Exception $e) {
                Log::warning(self::LOG_LABEL." Error. Shoe detail id: ".$item->data->id." could not be processed");
                Log::error(self::LOG_LABEL." ".$e);
                $not_processed[] = $item->data->id;
            }
        }
        Log::info(self::LOG_LABEL." Process finished.");

        return response()->json(["success" => $success_items, "errors" => $error_items, "not_processed" => $not_processed]);
    }

    public function update_stock(Request $request) {
        $request->user()->authorizeRoles(['admin']);

        Log::info("*****************************************");
        Log::info(self::LOG_LABEL." New request to update stock received. ".$request->getContent());

        $items = $request->input('data');
        $sucursal = $request->input('sucursal');
        
        
        // CHECK IF THERE IS PROCCES OPEN. If not, new one.
        Log::info(self::LOG_LABEL." Checking if there is reset process open for sucursal: {$sucursal}...");
        $process = StockReset::where('status', 0)->where('id_sucursal', $sucursal)->first();

        if (!$process) {
            Log::info(self::LOG_LABEL." New Process created.");
            $process = new StockReset();
            $process->id_sucursal = $sucursal;
            $process->id_user = Auth::id();
            $process->save();
        }
        else{
            Log::info(self::LOG_LABEL." Process exists: {$process}");
        }

        $success = [];
        $errors = [];

        foreach ($items as $item) {
            try {
                DB::beginTransaction();

                $sucursal_item = ShoeSucursalItem::where([
                    ['id_sucursal', $sucursal],
                    ['id_shoe_detail', $item['id']]])
                    ->firstOrFail();

                Log::info(self::LOG_LABEL." Updating stock for sucursal item: {$sucursal_item->id}");

                //Check if has been updated before
                $stock_reset_item = StockResetItem::where([
                    ['id_stock_reset', $process->id],
                    ['id_shoe_detail', $item['id']]])
                    ->first();
                
                if (!$stock_reset_item) {
                    $stock_reset_item = new StockResetItem();
                    $stock_reset_item->id_shoe_detail = $item['id'];
                    $stock_reset_item->id_stock_reset = $process->id;
                }

                //Update stock_reset_items
                Log::info(self::LOG_LABEL." Updating stock reset item for shoe detail id: {$item['id']}");
                $stock_reset_item->old_stock = $sucursal_item->stock;
                $stock_reset_item->new_stock = $sucursal_item->stock - $item['difference'];
                $stock_reset_item->save();
                
                //Update stock_sucursal_item
                Log::info(self::LOG_LABEL." Updating sucursal item for shoe detail id: {$item['id']}");
                $sucursal_item->stock -= $item['difference'];
                $sucursal_item->save();

                //Update shoeDetailStock
                Log::info(self::LOG_LABEL." Updating shoe detail id: {$item['id']}");
                if ($item['difference'] != 0) {
                    $shoe_detail = ShoeDetail::find($item['id']);
                    $shoe_detail->stock -= $item['difference'];
                    $shoe_detail->save();
                }

                DB::commit();
                $success[] = $item['id'];
                Log::info(self::LOG_LABEL." Success. Stock for sucursal item: {$sucursal_item->id} updated.");

            }
            catch(ModelNotFoundException $e){
                Log::error($e);
                DB::rollBack();
                $errors[] = $item['id'];
            }
            catch(Exception $e) {
                Log::error($e);
                DB::rollBack();
                $errors[] = $item['id'];
            }
        }
        Log::info(self::LOG_LABEL." Updating stock ended.");
        return response()->json(['success' => $success, 'errors' => $errors]);
    }

    public function reset_all_stock(Request $request) {
        $request->user()->authorizeRoles(['admin']);

        Log::info("*****************************************");
        Log::info(self::LOG_LABEL." New request to reset all stock received. ".$request->getContent());

        $sucursal = $request->input('sucursal');

        $status = self::STATUS_SUCCESS_TITLE;
        $message = 'El stock se puso en 0 satisfactoriamente.';
        
        // CHECK IF THERE IS PROCCES OPEN.
        Log::info(self::LOG_LABEL." Checking if there is reset process open for sucursal: {$sucursal}...");
        $process = StockReset::where('status', 0)->where('id_sucursal', $sucursal)->first();

        if (!$process) {
            Log::info(self::LOG_LABEL." New Process created.");
            $process = new StockReset();
            $process->id_sucursal = $sucursal;
            $process->id_user = Auth::id();
            $process->save();
        }
        else{
            Log::info(self::LOG_LABEL." Process exists: {$process}");
        }

        try {
            DB::beginTransaction();

            $shoe_details = ShoeDetail::whereNOTIn('id', function($query) use($process) {
                    $query->select('stock_reset_items.id_shoe_detail as id')
                        ->from('stock_reset_items')
                        ->where('stock_reset_items.id_stock_reset', $process->id);
                })->get();

            foreach($shoe_details as $shoe) {

                Log::debug(self::LOG_LABEL." Updating shoe detail: {$shoe->id}");

                $sucursal_item = ShoeSucursalItem::where([
                    ['id_shoe_detail', $shoe->id],
                    ['id_sucursal', $sucursal]
                ])->first();

                //New insert 
                Log::debug(self::LOG_LABEL." Inserting new row to stock_reset_items table");
                $new_reset = new StockResetItem();
                $new_reset->id_stock_reset = $process->id;
                $new_reset->id_shoe_detail = $shoe->id;
                $new_reset->new_stock = 0;
                $new_reset->old_stock = $sucursal_item->stock;
                $new_reset->save();

                //Update shoe detail
                $shoe->stock -= $sucursal_item->stock;
                //Update shoe sucursal item
                //$sucursal_item->stock = 0;

                //save all
                $shoe->save();

            }
            ShoeDetail::whereNOTIn('id', function($query) use($process) {
                $query->select('stock_reset_items.id_shoe_detail as id')
                    ->from('stock_reset_items')
                    ->where('stock_reset_items.id_stock_reset', $process->id);
            })->update(['stock' => 0]);
            Log::info(self::LOG_LABEL." Updated stock id shoe_sucursal_items");

            $process->status = 1;
            $process->save();
            
            Log::info(self::LOG_LABEL." Process finished.");

            DB::commit();
            
        }
        catch(ModelNotFoundException $e){
            Log::error($e);
            DB::rollBack();
            $status = STATUS_ERROR_TITLE;
            $message = 'Hubo un error inesperado al poner en 0 el stock.';
        }
        catch(Exception $e) {
            Log::error($e);
            DB::rollBack();
            $status = STATUS_ERROR_TITLE;
            $message = 'Hubo un error inesperado al poner en 0 el stock.';
        }

        return response()->json(['message' => $message, 'status' => $status]);
    }
}
