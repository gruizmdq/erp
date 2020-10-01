<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

use App\StockMovement;
use App\StockMovementItem;
use App\ShoeDetail;
use App\ShoeSucursalItem;

use Log;
use Exception;

class StockMovementController extends Controller
{
    const LOG_LABEL = '[STOCK MOVEMENTS API]';
    const STATUS_ERROR_TITLE = "Ups. Algo salió mal";
    const STATUS_SUCCESS_TITLE = "¡Bien papá!";

    public function get_movements(Request $request) {
        $request->user()->authorizeRoles(['admin']);


        $movements = StockMovement::select(
                         'stock_movements.*',
                         's1.name as sucursal_from',
                         's2.name as sucursal_to'
                        )
                        ->join('sucursals as s1','stock_movements.id_sucursal_from', 's1.id')
                        ->join('sucursals as s2','stock_movements.id_sucursal_to', 's2.id')
                        ->latest()->paginate(10);

        Log::info($movements);
        return response()->json($movements);
    }

    public function get_movement(Request $request, $id) {
        $request->user()->authorizeRoles(['admin']);
        $mov = StockMovement::findOrFail($id);
        $mov->sucursals = $mov->getSucursalNames();
        $mov->items = $mov->getItems();
        return $mov;
    }

    public function add_movements(Request $request) {
        $request->user()->authorizeRoles(['admin']);
        
        Log::info("*****************************************");
        Log::info(self::LOG_LABEL." Request to add movements received: ".$request->getContent());

        $items = $request->input('items');
        $from = $request->input('from');
        $to = $request->input('to');
        $qty = $request->input('qty');
        $description = $request->input('description', null);
        
        $statusCode = 200;
        $message = "¡La transferencia de stock se realizó correctamente!";
        $status = self::STATUS_SUCCESS_TITLE;

        try {

            DB::beginTransaction();

            Log::info(self::LOG_LABEL." Starting proccess...");

            //Create new movement
            $new_movement = new StockMovement();
            $new_movement->qty = $qty;
            $new_movement->id_sucursal_from = $from;
            $new_movement->id_sucursal_to = $to;
            $new_movement->status = 0;
            $new_movement->description = $description;
            $new_movement->save();

            //Updating items
            foreach ($items as $i) {
                //Get Shoe Detail to Update
                $shoe_detail = ShoeDetail::findOrfail($i['id']);
                //Get Stock on each sucursal
                $sucursal_items = $shoe_detail->shoeSucursalItem;

                Log::info(self::LOG_LABEL." Start updating shoe_detail: $shoe_detail->id");

                //UPDATING Stock SUCURSAL FROM
                for ($index = 0; $index < count($sucursal_items); $index++) {
                    if ($sucursal_items[$index]->id_sucursal == $from) {
                        if($sucursal_items[$index]->stock < $i['qty'])
                            //TODO OWN EXCEPTION
                            throw new Exception("There is not possible to move {$i['qty']}  items. The sucursal has less than {$i['qty']}.");
                        $sucursal_items[$index]->stock -= $i['qty'];
                        $sucursal_items[$index]->save();
                        Log::info(self::LOG_LABEL." Updated sucursal item (FROM) {$sucursal_items[$index]->id_sucursal} for shoe_detail: $shoe_detail->id");
                        break;
                    }
                }

                // No encontro sucursal from. Error, quiere decir que no tiene stock
                if ($index == count($sucursal_items)){
                    Log::error(self::LOG_LABEL." There was an error with shoe detail {$i['id']}. It's seems there is not stock at the sucursal.");
                    throw new Exception();
                }

                // Search and get if exists SUCURSAL TO
                for ($index = 0; $index < count($sucursal_items); $index++) {
                    if ($sucursal_items[$index]->id_sucursal == $to) {
                        $sucursal_items[$index]->stock += $i['qty'];
                        $sucursal_items[$index]->save();
                        Log::info(self::LOG_LABEL." Updated sucursal item (TO) {$sucursal_items[$index]->id_sucursal} for shoe_detail: $shoe_detail->id");
                        break;
                    }
                }

                // NEW 
                if ($index == count($sucursal_items)) {
                    $new = new ShoeSucursalItem();
                    $new->id_shoe_detail = $shoe_detail->id;
                    $new->id_sucursal = $to;
                    $new->stock = $i['qty'];
                    $new->save();
                    Log::info(self::LOG_LABEL." Created new sucursal item (TO) for shoe_detail: $shoe_detail->id");
                }

                //INSER MOVEMENT  ITEM
                $new_movement_item = new StockMovementItem();
                $new_movement_item->qty = $i['qty'];
                $new_movement_item->id_shoe_detail = $i['id'];
                $new_movement_item->id_stock_movement = $new_movement->id;
                $new_movement_item->save();

                Log::info(self::LOG_LABEL." Success. Updated stock for shoe_detail: {$i['id']}");

            }
            
            DB::commit();
            Log::info(self::LOG_LABEL." Success. Proccess finished");
            
        }
        catch (ModelNotFoundException $e) {
            Log::error(self::LOG_LABEL." ERROR. ShoeDetail item with not found.");
            $statusCode = 501;
            $message = "Hubo un error en la base de datos.";
            $status = self::STATUS_ERROR_TITLE;
            DB::rollBack();
        }
        catch (Exception $e) {
            Log::error(self::LOG_LABEL." ERROR. There was an error with. ".$e);
            $statusCode = 501;
            $message = "Hubo un error.";
            $status = self::STATUS_ERROR_TITLE;
            DB::rollBack();
        }
        return response()->json(['id_movement' => $new_movement->id, 'status' => $status, 'message'=> $message, 'statusCode' => $statusCode ]);
    }
}
