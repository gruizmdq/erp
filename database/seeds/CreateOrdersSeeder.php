<?php

use Illuminate\Database\Seeder;

use App\OrderSucursal;
use App\OrderPayment;
use App\OrderItem;
use App\Order;
use App\CashRegister;
use App\CashRegisterTurn;

class CreateOrdersSeeder extends Seeder
{
    public function run()
    {
        $new = new CashRegister();
        $new->date = date('Y-m-d');
        $new->id_sucursal = 1;
        $new->save();

        $new_turn = new CashRegisterTurn();
        $new_turn->id_cash_register = 4;
        $new_turn->id_cashier = 4;
        $start_cash = CashRegisterTurn::latest()->value('end_cash');
        if ($start_cash == null)
            $start_cash = 0;
        $new_turn->start_cash = $start_cash;
        $new_turn->save();

        for ($i = 0; $i < 1000; $i++) {
            try {
                DB::beginTransaction();
                
                $qty = random_int(1, 40);
                $subtotal = mt_rand(1000, 50000);

                $order = Order::create([
                    'id_user' => 1,
                    'id_client' => null,
                    'order_type' => 'App\OrderSucursal',
                    'qty' => $qty,
                    'subtotal' => $subtotal,
                    'total' => $subtotal,
                ]);
                
                //ORDER ITEMS
                for ($j = 0; $j < $qty; $j++) {

                    $item = OrderItem::create([
                            'id_order' => $order->order_id,
                            'id_shoe_detail' => random_int(4, 1000),
                            'qty' => 1,
                            'buy_price' => 1000,
                            'sell_price' => $subtotal / $qty,
                            'total' =>  $subtotal / $qty
                        ]);
                }
    
                //PAYMENT METHODS
                for ($i = 0; $i < random_int(1, 3); $i++) {
                    $payment = new OrderPayment();
                    $payment->id_order = $order->order_id;
                    $payment->id_payment_method = random_int(1, 4);
                    $payment->id_payment_card = 1;
                    $payment->id_payment_option = random_int(1, 5);
                    $payment->total = rand(100, 1200);
                    $payment->save();
                }
    
                //Create Sucursal order
                $id_cash = CashRegister::where('id_sucursal', 1)
                                ->whereDate('date', date('Y-m-d'))
                                ->value('id');
                $id_turn = CashRegisterTurn::where([
                                        ['id_cash_register', $id_cash],
                                        ['status', 0]
                            ])->value('id');
    
                $type_order = new OrderSucursal();
                $type_order->id = $order->order_id;
                $type_order->id_sucursal = 1;
                $type_order->id_seller = 4;
                $type_order->id_cashier = 4;
                $type_order->id_turn = $id_turn;
                $type_order->save();
    
            
                DB::commit();
    
            }
            catch (Exception $e) {
                DB::rollBack();
            }
        }

    }
}
