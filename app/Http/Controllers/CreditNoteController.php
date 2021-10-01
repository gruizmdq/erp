<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

use App\CreditNote;
use App\ShoeSucursalItem;

use Log;
use Exception;

class CreditNoteController extends Controller
{
    const LOG_LABEL = '[CREDIT NOTE API]';
    const STATUS_ERROR_TITLE = "Ups. Algo salió mal";
    const STATUS_SUCCESS_TITLE = "¡Bien papá!";
    const STATUS_SUCCESS_CODE = 200;
    
    public function createCreditNote(Request $request) {
        Log::info(self::LOG_LABEL." New request to create Credit Note: ".$request->getContent());
        
        $request->user()->authorizeRoles(['admin', 'cashier', 'seller']);
        $status = self::STATUS_SUCCESS_TITLE;
        $message = 'La order se creó correctamente';
        $statusCode = 200;

        try {
            $amount = $request->input('amount', null);

            if ($amount) {
                $new = new CreditNote();
                $new->amount = $amount;
                $new->status = 'CREATED';
                $new->id_user = Auth::id();
                $new->save();
            }
            else {
                throw new Exception;
            }
        }
        catch(Exception $e) {
            Log::error($e);
            $message = 'Hubo problemas al crear la nota de crédito.';
            $statusCode = 501;
            $status = self::STATUS_ERROR_TITLE;
            return response()->json(['creditNote' => null, 'status' => $status, 'message'=> $message, 'statusCode' => $statusCode ]);

        }
        
        return response()->json(['creditNote' => $new, 'status' => $status, 'message'=> $message, 'statusCode' => $statusCode ]);
    }

    public function createWithoutOrder(Request $request) {
        Log::info(self::LOG_LABEL." New request to create Credit Note without order: ".$request->getContent());
        
        $request->user()->authorizeRoles(['admin', 'cashier', 'seller']);
        $status = self::STATUS_SUCCESS_TITLE;
        $message = 'La nota de crédito se creó correctamente';
        $statusCode = 200;
        try {

            DB::beginTransaction();

            $amount = $request->input('data.amount', null);

            foreach($request->input('data.items', null) as $item) {
                ShoeSucursalItem::updateItem($item['id'], $this->get_cookie('id_sucursal'), -1);
            }

            if ($amount) {
                $new = new CreditNote();
                $new->amount = $amount;
                $new->status = 'CREATED';
                $new->id_user = Auth::id();
                $new->save();
            }
            else {
                throw new Exception;
            }

            DB::commit();
        }
        catch(Exception $e) {
            DB::rollBack();
            Log::error($e);
            $message = 'Hubo problemas al crear la nota de crédito.';
            $statusCode = 501;
            $status = self::STATUS_ERROR_TITLE;
            return response()->json(['creditNote' => null, 'status' => $status, 'message'=> $message, 'statusCode' => $statusCode ]);

        }
        
        return response()->json(['creditNote' => $new, 'status' => $status, 'message'=> $message, 'statusCode' => $statusCode ]);
    }

    private function get_cookie($cookie) {
        return Crypt::decrypt($_COOKIE[$cookie], false);
    }
}
