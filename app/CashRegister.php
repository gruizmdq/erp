<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CashRegister extends Model
{
    public const STATUS_OPEN = 0;

    public function turns() {
        return $this->hasMany('App\CashRegisterTurn', 'id_cash_register')->orderBy('status', 'desc');;
    }

    public static function get_open_turn_by_sucursal_id($id_sucursal) {
        return CashRegister::where([
            ['cash_registers.id_sucursal', $id_sucursal],
            ['cash_registers.status', self::STATUS_OPEN],
            ['cash_register_turns.status', self::STATUS_OPEN]])
            ->join('cash_register_turns', 'cash_register_turns.id_cash_register', 'cash_registers.id')
            ->first();
    }

}
