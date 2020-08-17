<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

use App\OrderPaymentMethod;
use App\OrderPaymentMethodCard;
use App\OrderPaymentMethodCardOption;
use App\OrderSucursal;
use App\OrderPayment;
use App\OrderItem;
use App\OrderReset;
use App\Order;

use App\CashRegister;
use App\CashRegisterTurn;

use App\ShoeSucursalItem;
use App\ShoeDetail;

use Exception;
use Log;

class OrderController extends Controller
{   
    const LOG_LABEL = '[ORDER API]';
    const STATUS_ERROR_TITLE = "Ups. Algo salió mal";
    const STATUS_SUCCESS_TITLE = "¡Bien papá!";
    const STATUS_SUCCESS_CODE = 200;

    public function index(Request $request) {
        $request->user()->authorizeRoles(['admin', 'cashier']);
        $cash = null;
        $turn = null;
        try {
            $cash = CashRegister::where([
                ['id_sucursal', $this->get_cookie('id_sucursal')],
                ['status', 0]
            ])->whereDate('date', date('Y-m-d'))->first();

            if ($cash) {
                $turn = CashRegisterTurn::where([
                    ['id_cash_register', $cash->id],
                    ['status', 0]
                ])->latest();
            }
        }
        catch (Exception $e) {
            Logg::error($e);
        }
        finally {
            if ($cash && $turn)
                return view('sell.home');
            return redirect('cash');
        }
    }

    public function get_payment_methods (Request $request) {
        return OrderPaymentMethod::get();
    }

    public function get_payment_method_cards (Request $request) {
        return OrderPaymentMethodCard::get();
    }

    public function get_payment_method_card_options (Request $request) {
        return OrderPaymentMethodCardOption::where('id_card', $request->input('id_card'))->get();
    }

    public function new_order (Request $request) {
        Log::info(self::LOG_LABEL." Request to create order: ".json_encode($request->input('order')));
        $data = $request->input('order');
        $id_sucursal = $this->get_cookie('id_sucursal');

        $status = self::STATUS_SUCCESS_TITLE;
        $message = 'La order se creó correctamente';
        $statusCode = 200;

        try {
            DB::beginTransaction();

            $order = Order::create([
                        'id_user' => Auth::id(),
                        'id_client' => $request->input('order.client', null),
                        'order_type' => 'App\OrderSucursal',
                        'qty' => $data['qty'],
                        'subtotal' => $data['subtotal'],
                        'total' => $data['total'],
                    ]);
            
            if ($request->input('order.orderDiscount', null)) {
                $id = $this->create_discount($request->input('order.orderDiscount'));
                $order->id_discount = $id;
                $order->save();
            }
            

            //ORDER ITEMS
            foreach ($data['items'] as $item) {
                Log::info(self::LOG_LABEL." Creating new order item (id: {$item['id']})");
                $new = OrderItem::create([
                        'id_order' => $order->order_id,
                        'id_shoe_detail' => $item['id'],
                        'qty' => $item['qty'],
                        'buy_price' => $item['buy_price'],
                        'sell_price' => $item['sell_price'],
                        'total' => $item['price']
                    ]);

                Log::info(self::LOG_LABEL." New order item (id: {$item['id']}) created");
                Log::info(self::LOG_LABEL." Updating sucursal stock for item (id: {$item['id']})");

                $sucursal_item = ShoeSucursalItem::where([
                                ['id_shoe_detail', $item['id']],
                                ['id_sucursal', $id_sucursal]
                                ])->first();
                $sucursal_item->stock -= $item['qty'];
                $sucursal_item->save();
                Log::info(self::LOG_LABEL." Sucursal stock for item (id: {$item['id']}) updated");
                
                $shoe_detail = ShoeDetail::where('id', $item['id'])->first();
                $shoe_detail->stock -= $item['qty'];
                $shoe_detail->save();
            }

            //PAYMENT METHODS
            foreach ($data['paymentMethods'] as $payment) {
                Log::info(self::LOG_LABEL." Creating new payment for order (id: {$order->order_id})");
                $new = new OrderPayment();
                $new->id_order = $order->order_id;
                $new->id_payment_method = $payment['method']['id'];
                $new->id_payment_card = $payment['card'] ? $payment['card']['id'] : null;
                $new->id_payment_option = $payment['option'] ? $payment['option']['id'] : null;
                $new->total = $payment['amount'];
                $new->save();
                Log::info(self::LOG_LABEL." New payment for order (id: {$order->order_id}) created");
            }

            //Create Sucursal order
            $id_cash = CashRegister::where('id_sucursal', $id_sucursal)
                            ->whereDate('date', date('Y-m-d'))
                            ->value('id');
            $id_turn = CashRegisterTurn::where([
                                    ['id_cash_register', $id_cash],
                                    ['status', 0]
                        ])->value('id');

            $type_order = new OrderSucursal();
            $type_order->id = $order->order_id;
            $type_order->id_sucursal = $id_sucursal;
            $type_order->id_seller = $data['seller'];
            $type_order->id_cashier = $data['cashier'];
            $type_order->id_turn = $id_turn;
            $type_order->save();

        
            DB::commit();

            Log::info(self::LOG_LABEL." SUCCESS. Request to create new order completed.");
        }
        catch (Exception $e) {
            Log::error(self::LOG_LABEL." Error.");
            Log::error($e);
            DB::rollBack();

        }

        return response()->json(['status' => $status, 'message'=> $message, 'statusCode' => $statusCode ]);
    }

    public function new_reset(Request $request) {
        Log::info(self::LOG_LABEL." Request to reset order: ".$request->getContent());
        $id_sucursal = $this->get_cookie('id_sucursal');

        try {
            OrderReset::create([
                    'id_user' => Auth::id(),
                    'id_seller' => $request->input('id_seller'),
                    'id_cashier' => $request->input('id_cashier'),
                    'id_sucursal' => $id_sucursal,
            ]);

            Log::info(self::LOG_LABEL." SUCCESS. Request to add new order reset completed.");
        }
        catch (Exception $e) {
            Log::error(self::LOG_LABEL." Error inserting new reset.");
            Log::error($e);
        }

        return self::STATUS_SUCCESS_CODE;
    }

    private function create_discount ($data) {
        return true;
    }

    private function get_cookie($cookie) {
        return Crypt::decrypt($_COOKIE[$cookie], false);
    }
}
