<template>
    <div>
        <div v-if="!cashRegister" class="card p-3 text-center">
            <h3>No se abrió nigún turno hoy.</h3>
            <button @click="newCashRegister" style="margin: auto; max-width: 200px" type="button" class="btn btn-sm btn-primary">Abrir Caja</button>
        </div>
        <div v-if="cashRegister && !turn" class="card p-3 text-center">
            <h3>No hay ningún turno abierto.</h3>
            <button @click="newTurn" style="margin: auto; max-width: 200px" type="button" class="btn btn-sm btn-primary">Abrir Turno</button>
        </div>
        <div v-if="cashRegister && turn">
            <div class="row">
                <div class="col-md-3">
                    <div class="card p-3">
                        <p class="mb-0">Ventas</p>
                        <hr>
                        <p>Ventas Contado: <strong>${{ cashSales}}</strong></p>
                        <p>Ventas Tarjeta: <strong>${{ cardSales }}</strong></p>
                        <p>Cuenta Corriente: <strong>$123.000</strong></p>
                        <hr>
                        <p>Total: <strong>${{ totalSales }}</strong></p>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card p-3">
                        <p class="mb-0">Egresos de Caja <button @click="showNewMovementForm(0)" type="button" class="btn btn-sm btn-outline-primary">Nuevo</button></p>
                        <hr>
                        <p v-for="movement in outcomes" :key="movement.id">{{ movement.description}}: <strong>${{ movement.amount }}</strong></p>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card p-3">
                        <p class="mb-0">Ingresos de Caja <button @click="showNewMovementForm(1)" type="button" class="btn btn-sm btn-outline-primary">Nuevo</button></p>
                        <hr>
                        <p v-for="movement in incomes" :key="movement.id">{{ movement.description}}: <strong>${{ movement.amount }}</strong></p>
                    </div>
                </div>

                <div class="col-md-3">
                    <button type="button" class="btn btn-sm btn-primary" @click="getTotalTurnCash">Cerrar Turno</button>
                </div>

            </div>
            <hr>
            <div class="row">
                <div class="col-md-9">
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label for="inputEmail4">Z</label>
                            <input type="text" class="form-control" id="inputEmail4">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="inputPassword4">Guardar</label>
                            <input type="password" class="form-control" id="inputPassword4">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="inputPassword4">Diferencia</label>
                            <input type="password" class="form-control" id="inputPassword4">
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-sm btn-primary">Cerrar Día</button>
                </div>
            </div>

        </div>

        <md-dialog-alert
        :md-active.sync="showAlert"
        :md-title="alertTitle"
        :md-content="alertContent" />

        <md-dialog :md-active.sync="showDialog">
            <md-dialog-title>Nuevo {{ movementsType[newMovementItem.type] }} de caja</md-dialog-title>
            <div class="form-group p-1">
                <label for="inputPassword4">Monto</label>
                <input v-model='newMovementItem.amount' type="number" class="form-control">
            </div>
            <div class="form-group p-1">
                <label for="inputPassword4">Descripcion</label>
                <input v-model="newMovementItem.description" type="text" class="form-control">
            </div>
            <md-dialog-actions>
                <md-button class="md-primary" @click="showDialog = false">Cancelar</md-button>
                <md-button class="md-primary" @click="newMovement">Enviar</md-button>
            </md-dialog-actions>
        </md-dialog>

        <md-dialog :md-active.sync="showCloseTurnDialog">
            <md-dialog-title>Cerrar Turno</md-dialog-title>
            <div class="p-3">
                <p>Caja: $<strong>{{ totalTurnCash }}</strong></p>
                <div class="form-group p-1">
                    <label for="inputPassword4">Ajustar Caja</label>
                    <input v-model.number='turnCorrection' type="number" class="form-control">
                </div>
            </div>
            <md-dialog-actions>
                <md-button class="md-primary" @click="showCloseTurnDialog = false">Cancelar</md-button>
                <md-button class="md-primary" @click="closeTurn">Enviar</md-button>
            </md-dialog-actions>
        </md-dialog>
    </div>
</template>
<script>
export default {
    name: "CashComponent",
    data() {
        return {
            cashRegister: null,
            turn: null,
            showAlert: false,
            alertTitle: '',
            alertContent: '',
            newMovementItem: {},
            showDialog: null,
            movementsType: ['Egreso', 'Ingreso'],

            showCloseTurnDialog: false,
            turnCorrection: 0,
            totalTurnCash: 0,
        }
    },
    computed: {
        outcomes() {
            return this.turn.movements != null ? this.turn.movements.filter(item => item.type == 0) : []
        },
        incomes() {
            return this.turn.movements != null ? this.turn.movements.filter(item => item.type == 1) : []
        },
        cashSales() {
            return this.getSales(1).toLocaleString(2)
        },
        cardSales() {
            return this.getSales(2).toLocaleString(2)
        },
        totalSales() {
            return (this.getSales(1) + this.getSales(2)).toLocaleString(2)
        }
    },
    methods: {
        getData() {
            axios.get('/api/cash/cash_register').then(response => {
                console.log(response.data)
                if (response.data.cash_register)
                    this.cashRegister = response.data.cash_register;
                if (response.data.turn)
                    this.turn = response.data.turn
            });
        },
        newCashRegister() {
            axios.post('/api/cash/cash_register').then(response => {
                if (response.data.statusCode == 200) {
                    this.cashRegister = response.data.cash_register;
                    this.turn = response.data.turn
                }
                this.showAlert = true
                this.alertTitle = response.data.status
                this.alertContent = response.data.message
            });
        },
        newTurn() {
            if (this.cashRegister) {
                axios.post('/api/cash/turn', {id_cash_register: this.cashRegister.id}).then(response => {
                    if (response.data.statusCode == 200) {
                        this.turn = response.data.turn
                    }
                    this.showAlert = true
                    this.alertTitle = response.data.status
                    this.alertContent = response.data.message
                });
            }
        },
        newMovement() {
            axios.post('/api/cash/movement', {movement: this.newMovementItem}).then(response => {
                this.newMovementItem = {}
                this.showAlert = true
                this.alertTitle = response.data.status
                this.alertContent = response.data.message
            });
        },
        showNewMovementForm($type) {
            this.showDialog = true
            this.newMovementItem.type = $type
        },
        getTotalTurnCash() {
            axios.get('/api/cash/turn/cash').then(response => {
                this.totalTurnCash = parseFloat(response.data)
                this.showCloseTurnDialog = true
            });
        },
        closeTurn() {
            axios.put('/api/cash/turn/', { id_turn: this.turn.id, end_cash:  this.totalTurnCash - this.turnCorrection, correction: this.turnCorrection }).then(response => {
                this.showCloseTurnDialog = false
                this.showAlert = true
                this.alertTitle = response.data.status
                this.alertContent = response.data.message
                if (response.data.statusCode == 200)
                    this.turn = null
            }); 
        },
        getSales(type) {
            var total = 0

            if (this.turn && this.turn.payments) {
                this.turn.payments.forEach( item => {
                    if (item[0].id_payment_method == type)
                        total += item[0].total
                })
            }
            return total
        }
    },
    created() {
        this.getData()
    }

}
</script>