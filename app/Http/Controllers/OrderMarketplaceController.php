<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\OrderSucursal;
use App\OrderPaymentMethod;
use App\OrderPayment;
use App\OrderItem;
use App\OrderReset;
use App\OrderMarketplace;
use App\Order;
use App\User;

use App\ShoeSucursalItem;
use App\ShoeDetail;
use App\Sucursal;
use App\StockOrderChange;

use App\CreditNote;

use Exception;
use Log;

class OrderMarketplaceController extends Controller
{
    const LOG_LABEL = '[ORDER MARLETPLACE API]';
    const STATUS_ERROR_TITLE = "Ups. Algo salió mal";
    const STATUS_SUCCESS_TITLE = "¡Bien papá!";
    const STATUS_SUCCESS_CODE = 200;
    const PAGINATE = 5;

    public function new_order (Request $request) {
        $request->user()->authorizeRoles(['admin', 'seller']);
        Log::info(self::LOG_LABEL." Request to create order: ".json_encode($request->input('data')));

        $data = $request->input('data');

        $status = self::STATUS_SUCCESS_TITLE;
        $message = 'La order se creó correctamente';
        $statusCode = 200;
        $results = [];

        try {
            DB::beginTransaction();

            $order = Order::create([
                        'id_user' => Auth::id(),
                        'id_client' => $request->input('client'),
                        'order_type' => 'App\OrderMarketplace',
                        'qty' => count($data['items']),
                        'subtotal' => $data['subtotal'],
                        'total' => $data['total']+$data['delivery'],
                    ]);

            //ORDER ITEMS
            foreach ($data['items'] as $item) {
                Log::info(self::LOG_LABEL." Creating new order item (id: {$item['id']})");
                $detailItem = ShoeDetail::where([
                    ['id_shoe', $item['id']],
                    ['id_color', $item['color']],
                    ['number', $item['number']]
                    ])->first();

                $new = new OrderItem([
                        'id_order' => $order->order_id,
                        'id_shoe_detail' => $detailItem->id,
                        'qty' => $item['qty'],
                        'buy_price' => $detailItem->buy_price,
                        'sell_price' => $item['sell_price'],
                        'total' => $item['sell_price']
                    ]);

                Log::info(self::LOG_LABEL." New order item (id: {$detailItem->id}) created");
                Log::info(self::LOG_LABEL." Updating sucursal stock for item (id: {$detailItem->id})");
                
                
                $sucursal_item = ShoeSucursalItem::where([
                                ['id_shoe_detail', $detailItem->id],
                                ['id_sucursal', 1]
                                ])->first();

                if (!$sucursal_item || $sucursal_item->stock == 0) {
                    $sucursal_item = ShoeSucursalItem::where([
                        ['id_shoe_detail', $detailItem->id],
                        ['stock', '>', 0]
                        ])->first();
                }

                $sucursal_item->stock -= $item['qty'];
                $sucursal_item->save();

                $sucursal_name = Sucursal::where('id', $sucursal_item->id_sucursal)->value('name');
                $new->sucursal = $sucursal_name;
                $new->save();

                Log::info(self::LOG_LABEL." Sucursal stock for item (id: {$detailItem->id}) updated");
                
                $detailItem->stock -= $item['qty'];
                $detailItem->save();

                $results[] = ['shoe' => $detailItem->getDescription(), 'sucursal' => $sucursal_name];
            }

            //PAYMENT METHOD
            /*
            Log::info(self::LOG_LABEL." Creating new payment for order (id: {$order->order_id})");
            $new = new OrderPayment();
            $new->id_order = $order->order_id;
            $new->id_payment_method = OrderPaymentMethod::where('name', 'Efectivo')->value('id');
            $new->id_payment_card = null;
            $new->id_payment_option = null;
            $new->coupon = null;
            $new->total = $order->total;
            $new->save();
            Log::info(self::LOG_LABEL." New payment for order (id: {$order->order_id}) created");
            */
            //Create Marketplace order
            $type_order = new OrderMarketplace();
            $type_order->id = $order->order_id;
            $type_order->id_seller = Auth::id();
            $type_order->client = $data['client'];
            $type_order->phone = $data['phone'];
            $type_order->address = $data['address'];
            $type_order->zone = strtoupper($data['zone']);
            $type_order->delivery = $data['delivery'];
            $type_order->comments = $data['comments'];
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
        return response()->json(['results' => $results, 'status' => $status, 'message'=> $message, 'statusCode' => $statusCode ]);
    }

    public function get_orders(Request $request) {
        $request->user()->authorizeRoles(['admin', 'seller']);
        $user = false;
        if (!Auth::user()->hasRole('admin'))
            $user = Auth::id();
        $orders = Order::get_marketplace_orders(self::PAGINATE, $user);
        return $orders;
    }

    public function get_order(Request $request, $id) {
        $request->user()->authorizeRoles(['admin', 'seller']);

        $query = Order::where('order_id', $id);

        if (!Auth::user()->hasRole('admin')) {
            $query->where('id_user', Auth::id());
        }

        $order = $query->firstOrFail();
        $order->items = $order->get_items();
        $order->orderable;
        $order->seller = User::where('id', $order->orderable->id_seller)->value('name');
        return $order;
    }

    public function delete_orders(Request $request) {
        $request->user()->authorizeRoles(['admin']);

        Log::info(self::LOG_LABEL." Request to delete order: ".$request->getContent());

        $data = $request->input('orders');

        $status = self::STATUS_SUCCESS_TITLE;
        $message = 'La order se borró correctamente';
        $statusCode = 200;
        $results = [];

        try {
            DB::beginTransaction();
            foreach ($data as $id_order) {
                $order = Order::findOrFail($id_order);

                Log::info(self::LOG_LABEL." Order with id {$id_order} found: {$order}");

                $order_items = $order->order_items;
                foreach ($order_items as $item) {
                    Log::info(self::LOG_LABEL." Restoring stock for order item with id: {$item->id}");
                    
                    $sucursal_item = ShoeSucursalItem::where([
                        ['id_shoe_detail', $item->id_shoe_detail],
                        ['id_sucursal', 1]])
                        ->first();

                    $sucursal_item->stock += $item->qty;
                    $sucursal_item->save();

                    $shoe_detail = ShoeDetail::findOrFail($item->id_shoe_detail);
                    $shoe_detail->stock += $item->qty;
                    Log::info(self::LOG_LABEL." Stock for order item with id: {$item->id} restored.");
                    
                    $shoe_detail->save();

                    $item->delete();
                }
                $order_marketplace = OrderMarketplace::findOrFail($id_order);
                $order_marketplace->delete();
                $order->delete();

                Log::info(self::LOG_LABEL." Order with id: {$id_order} deleted.");

            }
            DB::commit();
            Log::info(self::LOG_LABEL." Success. Orders have been deleted.");
        }
        catch (Exception $e) {
            Log::error(self::LOG_LABEL." Error. {$e}");
            DB::rollBack();
            $message = 'Hubo problemas al borrar la venta.';
            $statusCode = 501;
            $status = self::STATUS_ERROR_TITLE;
        }
        return response()->json(['status' => $status, 'message'=> $message, 'statusCode' => $statusCode ]);

    }

    public function new_item_change(Request $request) {
        $request->user()->authorizeRoles(['admin', 'seller']);

        Log::info(self::LOG_LABEL." Request to change order item: ".$request->getContent());

        $status = self::STATUS_SUCCESS_TITLE;
        $message = 'El cambio se hizo correctamente';
        $statusCode = 200;

        //TODO ADMITIR MAS DE UN CAMBIO!
        //TODO: check time expiration!
        try {
            DB::beginTransaction();
            $old = $request->input('data.oldItem');
            $order_item = OrderItem::findOrFail($old['id']);
            $order = OrderMarketplace::findOrFail($order_item->id_order);

            Log::info(self::LOG_LABEL." Old order item: {$order_item}");

            $new_shoe_detail = ShoeDetail::findOrFail($request->input('data.newItem.id_shoe_detail'));
            Log::info(self::LOG_LABEL." New shoe detail: {$new_shoe_detail}");

            //Check si es necesario crear nota de creidot
            $amount = $new_shoe_detail->sell_price + $request->input('data.delivery') - $order_item->sell_price;
            if ($amount < 0) {
                $new_credit_note = CreditNote::create([
                    'id_user' => Auth::id(),
                    'amount' => $amount,
                ]);
                Log::info($new_credit_note);
                Log::info($new_credit_note->id);
                $order->comments = $order->comments.". Crear nota de crédito NRO ".$new_credit_note->id." por $".abs($amount);
            }
            else {
                $order->comments = $order->comments.". El cliente debe pagar $".abs($amount);
            }
            $order->delivery += $request->input('data.delivery');
            //order vs ordermarketplace
            $order_parent = Order::findOrFail($order->id);
            $order_parent->subtotal += $amount;
            $order_parent->total += $amount;
            $order_parent->save();
            $order->save();
            
            //Restore Stock
            $new_sucursal_item = ShoeSucursalItem::where([
                ['id_shoe_detail', $new_shoe_detail->id],
                ['id_sucursal', 1]
                ])->first();

            if ($new_sucursal_item || $new_sucursal_item->stock == 0) {
                $new_sucursal_item = ShoeSucursalItem::where([
                    ['id_shoe_detail', $new_shoe_detail->id],
                    ['stock', '>', 0]
                    ])->first();
            }

            $new_sucursal_item->stock -= 1;
            $new_shoe_detail->stock -= 1;
            $new_shoe_detail->save();
            $new_sucursal_item->save();

            $old_sucursal_item = ShoeSucursalItem::where([
                ['id_shoe_detail', $order_item->id_shoe_detail],
                ['id_sucursal', 1]
            ])->first();
            $old_sucursal_item->stock += 1;
            $old_shoe_detail = ShoeDetail::findOrFail($order_item->id_shoe_detail);
            $old_shoe_detail->stock += 1;
            $old_shoe_detail->save();

            //TOOD delete item y crear nuevo.
            $new_order_item = new OrderItem();
            $new_order_item->id_order = $order->id;
            $new_order_item->total = $new_shoe_detail->sell_price;
            $new_order_item->id_shoe_detail = $new_shoe_detail->id;
            $new_order_item->buy_price = $new_shoe_detail->buy_price;
            $new_order_item->sell_price = $new_shoe_detail->sell_price;
            $new_order_item->qty = 1;
            $new_order_item->sucursal = Sucursal::where('id', $new_sucursal_item->id_sucursal)->value('name');
            $new_order_item->save();

            $order_item->delete();

            //hacer registro del cambio
            $new_change = new StockOrderChange();
            $new_change->id_user = Auth::id();
            $new_change->id_shoe_detail_old = $order_item->id_shoe_detail;
            $new_change->id_shoe_detail_new = $new_shoe_detail->id;
            $new_change->save();
            

            DB::commit();

            return $order->comments;
        }
        catch(Exception $e) {
            Log::error(self::LOG_LABEL." Error. {$e}");
            DB::rollBack();
            $message = 'Hubo problemas al borrar la venta.';
            $statusCode = 501;
            $status = self::STATUS_ERROR_TITLE;
        }
        if ($order)
            $comments = $order->comments;
        return response()->json(['order_comments' => $comments, 'status' => $status, 'message'=> $message, 'statusCode' => $statusCode ]);

    }
}
