<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use App\CashRegister;
use App\CashRegisterTurn;
use App\CashRegisterMovement;

use App\OrderPaymentMethod;
use App\OrderPayment;

use Log;

class CashRegisterController extends Controller
{
    const LOG_LABEL = '[CASH REGISTER API]';
    const STATUS_ERROR_TITLE = "Ups. Algo salió mal";
    const STATUS_SUCCESS_TITLE = "¡Bien papá!";

    public function get_cash_register(Request $request) {
        $request->user()->authorizeRoles(['admin', 'cashier']);

        if ($request->input("id", null)) {
            $cash_register = CashRegister::find($request->input('id'));
            $turn = CashRegisterTurn::where([
                                            ['id_cash_register', $cash_register->id],
                                            ['status', 0]
                                            ])->first();
        }
        else {
            $cash_register = CashRegister::where('id_sucursal', $this->get_cookie('id_sucursal'))
                                        ->whereDate('date', date('Y-m-d'))
                                        ->first();
            if ($cash_register) {
                $turn = CashRegisterTurn::where([
                                                ['id_cash_register', $cash_register->id],
                                                ['status', 0]
                                                ])->first();
                if ($turn != null){                               
                    $turn->movements;
                    $payments = [];
                    foreach ($turn->orders as $order) {
                        $payments[] = $order->order->payments;
                    }
                    $turn->payments = $payments;
                }
            }
            else 
                $turn = null;
        }
        return response()->json(["cash_register" => $cash_register, "turn" => $turn]);
    }

    public function new_cash_register(Request $request) {
        $request->user()->authorizeRoles(['admin', 'cashier']);

        Log::info(self::LOG_LABEL." new request to open cash register");

        $status = self::STATUS_SUCCESS_TITLE;
        $message = 'Nuevo turno creado correctamente';
        $statusCode = 200;

        try {
            $new = new CashRegister();
            $new->date = date('Y-m-d');
            $new->id_sucursal = $this->get_cookie('id_sucursal');
            $new->save();

            $new_turn = new CashRegisterTurn();
            $new_turn->id_cash_register = $new->id;
            $new_turn->id_cashier = Auth::id();
            $start_cash = CashRegisterTurn::latest()->value('end_cash');
            if ($start_cash == null)
                $start_cash = 0;
            $new_turn->start_cash = $start_cash;
            $new_turn->save();
        }
        catch (Exception $e) {
            Log::error(self::LOG_LABEL." Error when create new cash register or turn");
            Log::error($e);
            $statusCode = 501;
            $message = "Hubo un error en la base de datos.";
            $new = null;
            $new_turn = null;
        }
        finally {
            return response()->json(["cash_register" => $new, "turn" => $new_turn, "status" => $status, "message" => $message, 'statusCode' => $statusCode]);
        }
    }

    public function get_total_turn_cash(Request $request) {
        $request->user()->authorizeRoles(['admin', 'cashier']);

        $id_cash = CashRegister::where('id_sucursal', $this->get_cookie('id_sucursal'))
                            ->whereDate('date', date('Y-m-d'))
                            ->value('id');
        $turn = CashRegisterTurn::where([
                                ['id_cash_register', $id_cash],
                                ['status', 0]
                                ])->first();
        
        $total_cash = $turn->start_cash;

        foreach($turn->movements as $movement) {
            $total_cash = $movement->type == CashRegisterMovement::OUTCOME_TYPE ? $total_cash - $movement->amount : $total_cash + $movement->amount;
        }

        foreach($turn->orders as $order) {
            $total_cash += OrderPayment::where([
                ['id_order', $order->id],
                ['id_payment_method', OrderPaymentMethod::CASH_PAYMENT_TYPE]
            ])->sum('total');
        }

        return $total_cash;
    }

    public function new_turn(Request $request) {
        $request->user()->authorizeRoles(['admin', 'cashier']);

        Log::info(self::LOG_LABEL." new request to open new turn");

        $status = self::STATUS_SUCCESS_TITLE;
        $message = 'Nuevo turno creado correctamente';
        $statusCode = 200;

        try {
            $new_turn = new CashRegisterTurn();
            $new_turn->id_cash_register = $request->input('id_cash_register');
            $new_turn->id_cashier = Auth::id();
            $start_cash = CashRegisterTurn::latest()->value('end_cash');
            if ($start_cash == null)
                $start_cash = 0;
            $new_turn->start_cash = $start_cash;
            $new_turn->save();
        }
        catch (Exception $e) {
            Log::error(self::LOG_LABEL." Error when create new turn");
            Log::error($e);
            $statusCode = 501;
            $message = "Hubo un error en la base de datos.";
            $new_turn = null;
        }
        finally {
            return response()->json(["turn" => $new_turn, "status" => $status, "message" => $message, 'statusCode' => $statusCode]);
        }
    }

    public function close_turn(Request $request) {
        $request->user()->authorizeRoles(['admin', 'cashier']);

        Log::info(self::LOG_LABEL." new request to close new turn: ".$request->getContent());

        $status = self::STATUS_SUCCESS_TITLE;
        $message = 'Turno cerrado correctamente';
        $statusCode = 200;

        try {
            $turn = CashRegisterTurn::where('status', 0)->findOrFail($request->input('id_turn'));
            $turn->end_cash = $request->input('end_cash');
            $turn->correction = $request->input('correction');
            $turn->status = 1;
            $turn->save();
            Log::info(self::LOG_LABEL." Success. Update proccess finished");
        }
        catch (ModelNotFoundException $e) {
            Log::error(self::LOG_LABEL." There was an error with turn id");
            Log::error($e);
            $statusCode = 501;
            $message = "Hubo un error en la base de datos.";
        }
        catch (Exception $e) {
            Log::error(self::LOG_LABEL." Error when update turn");
            Log::error($e);
            $statusCode = 502;
            $message = "Hubo un error.";
        }
        finally {
            return response()->json(["status" => $status, "message" => $message, 'statusCode' => $statusCode]);
        }
    }

    private function get_cookie($cookie) {
        return Crypt::decrypt($_COOKIE[$cookie], false);
    }
}