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
use App\OrderDiscount;
use App\OrderChange;
use App\Order;

use App\CashRegister;
use App\CashRegisterTurn;

use App\ShoeSucursalItem;
use App\ShoeDetail;
use App\Sucursal;

use Exception;
use Log;

class OrderController extends Controller
{   
    const LOG_LABEL = '[ORDER API]';
    const STATUS_ERROR_TITLE = "Ups. Algo salió mal";
    const STATUS_SUCCESS_TITLE = "¡Bien papá!";
    const STATUS_SUCCESS_CODE = 200;

    const PAGINATE_SIZE = 50;    

    public function index(Request $request) {
        $request->user()->authorizeRoles(['admin', 'cashier']);
        $cash = null;
        $turn = null;
        try {
            $cash = CashRegister::where([
                ['id_sucursal', $this->get_cookie('id_sucursal')],
                ['status', CashRegister::STATUS_OPEN]
            ])->first();

            if ($cash) {
                $turn = CashRegisterTurn::where([
                    ['id_cash_register', $cash->id],
                    ['status', CashRegister::STATUS_OPEN]
                ])->first();
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

    public function get_order(Request $request, $id) {
        $request->user()->authorizeRoles(['admin', 'cashier']);

        try {
            $order = Order::findOrFail($id);
            $items = OrderItem::select([
                                        'order_items.buy_price',
                                        'order_items.sell_price',
                                        'order_items.total',
                                        'order_items.qty',
                                        'shoe_details.number',
                                        'shoes.code',
                                        'shoe_colors.name as color',
                                        'shoe_brands.name as brand'
                                    ])
                                    ->join('shoe_details', 'order_items.id_shoe_detail', 'shoe_details.id')
                                    ->join('shoes', 'shoe_details.id_shoe', 'shoes.id')
                                    ->join('shoe_brands', 'shoes.id_brand', 'shoe_brands.id')
                                    ->join('shoe_colors', 'shoe_details.id_color', 'shoe_colors.id')
                                    ->where('order_items.id_order', $id)
                                    ->get();
            LOG::info($items);
            $payments = OrderPayment::select([
                                        'order_payments.total',
                                        'order_payment_methods.name as payment_method',
                                        'order_payment_method_cards.name as payment_card',
                                        'order_payment_method_card_options.installments',
                                        'order_payment_method_card_options.charge'
                                    ])
                                    ->join('order_payment_methods', 'order_payments.id_payment_method', 'order_payment_methods.id')
                                    ->leftJoin('order_payment_method_cards', 'order_payments.id_payment_card', 'order_payment_method_cards.id')
                                    ->leftJoin('order_payment_method_card_options', 'order_payments.id_payment_option', 'order_payment_method_card_options.id')
                                    ->where('order_payments.id_order', $id)
                                    ->get();
            $order->description = $order->orderable->getDescription();
            $order->items = $items;
            $order->payments;
            return $order;
            
        }
        catch (ModelNotFoundException $e) {
            Log::error(self::LOG_LABEL." ERROR. Order with $id not found.");
        }
        catch (Exception $e) {
            Log::error(self::LOG_LABEL." ERROR. There was an error with id: $id");
            Log::error($e);
        }
    }

    public function get_orders(Request $request) {
        $request->user()->authorizeRoles(['admin', 'cashier']);
        
        $filters = json_decode($request->input('filters'), true);
        $type = $request->input('type');

        try {
            $orders = Order::get_orders($filters, $type);
        }
        catch (Exception $e) {
            Log::error(self::LOG_LABEL." ERROR. Error performing get action.");
            Log::error($e);
            return response()->json(['status' => 'asda', 'message'=> 'message', 'statusCode' => 501 ]);
        }
        return $orders;
    }
    public function get_payment_methods (Request $request) {
        $request->user()->authorizeRoles(['admin', 'cashier']);

        return OrderPaymentMethod::get();
    }

    public function get_payment_method_cards (Request $request) {
        $request->user()->authorizeRoles(['admin', 'cashier']);

        return OrderPaymentMethodCard::get();
    }

    public function get_payment_method_card_options (Request $request) {
        $request->user()->authorizeRoles(['admin', 'cashier']);

        return OrderPaymentMethodCardOption::where('id_card', $request->input('id_card'))->get();
    }

    public function new_order (Request $request) {
        $request->user()->authorizeRoles(['admin', 'cashier']);

        Log::info(self::LOG_LABEL." Request to create order: ".json_encode($request->input('order')));
        $data = $request->input('order');
        $id_sucursal = $this->get_cookie('id_sucursal');

        $status = self::STATUS_SUCCESS_TITLE;
        $message = 'La order se creó correctamente';
        $statusCode = 200;

        try {
            DB::beginTransaction();
            if ($request->input('order.orderType') == 'OrderSucursal') {
                $orderType = 'App\OrderSucursal';
            }
            else if($request->input('order.orderType') == 'OrderChange') {
                $orderType = 'App\OrderChange';
            }

            $order = Order::create([
                        'id_user' => Auth::id(),
                        'id_client' => $request->input('order.client', null),
                        'order_type' => $orderType,
                        'qty' => $data['qty'],
                        'subtotal' => $data['subtotal'],
                        'total' => $data['total'],
                    ]);
            
            if ($request->input('order.orderDiscount', null)) {
                $id = OrderDiscount::create([
                        'id_user' => Auth::id(),
                        'description' => $request->input('order.orderDiscount.comments', null),
                        'amount' => $request->input('order.orderDiscount.amount', 0)
                    ])->id;
                $order->id_discount = $id;
                $order->save();
            }
            

            //ORDER ITEMS
            foreach ($data['items'] as $item) {
                Log::info(self::LOG_LABEL." Creating new order item (id: {$item['id']})");
                OrderItem::createItem($order->order_id, $item);
                
                Log::info(self::LOG_LABEL." New order item (id: {$item['id']}) created");
                ShoeSucursalItem::updateItem($item['id'], $id_sucursal, $item['qty']*-1);

            }

            //ORDER ITEMS CHANGES
            foreach ($data['itemsForChange'] as $item) {
                Log::info(self::LOG_LABEL." Creating new order item (id: {$item['id']})");
                OrderItem::createItem($order->order_id, $item, 'IN');
                
                Log::info(self::LOG_LABEL." New order item (id: {$item['id']}) created");
                ShoeSucursalItem::updateItem($item['id'], $id_sucursal, $item['qty']);

            }

            //PAYMENT METHODS
            foreach ($data['paymentMethods'] as $payment) {
                Log::info(self::LOG_LABEL." Creating new payment for order (id: {$order->order_id})");
                $new = new OrderPayment();
                $new->id_order = $order->order_id;
                $new->id_payment_method = $payment['method']['id'];
                $new->id_payment_card = $payment['card'] ? $payment['card']['id'] : null;
                $new->id_payment_option = $payment['option'] ? $payment['option']['id'] : null;
                $new->coupon = $payment['coupon'];
                $new->total = $payment['amount'];
                $new->save();
                Log::info(self::LOG_LABEL." New payment for order (id: {$order->order_id}) created");
            }

            switch ($orderType) {
                case 'App\OrderSucursal': 
                    $type_order = new OrderSucursal();
                    $type_order->id = $order->order_id;
                    $type_order->id_sucursal = $id_sucursal;
                    $type_order->id_seller = $data['seller'];
                    $type_order->id_cashier = $data['cashier'];
                    $type_order->id_turn = CashRegister::get_open_turn_by_sucursal_id($id_sucursal)->id;
                    break;
                case 'App\OrderChange':
                    $type_order = new OrderChange();
                    $type_order->id = $order->order_id;
                    $type_order->id_sucursal = $id_sucursal;
                    $type_order->id_seller = $data['seller'];
                    $type_order->id_cashier = $data['cashier'];
                    $type_order->id_turn = CashRegister::get_open_turn_by_sucursal_id($id_sucursal)->id;
                    break;
            }
            
            $type_order->save();

        
            DB::commit();

            Log::info(self::LOG_LABEL." SUCCESS. Request to create new order completed.");
        }
        catch (Exception $e) {
            Log::error(self::LOG_LABEL." Error.");
            Log::error($e);
            $message = 'Hubo problemas al crear la venta.';
            $statusCode = 501;
            $status = self::STATUS_ERROR_TITLE;
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
