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
            <div class="card mb-4 wow fadeIn">
                <div class="card-body d-sm-flex justify-content-between">
                    <h4 class="mb-2 mb-sm-0 pt-1">
                        <a>Sucursal</a>
                        <span v-if="sucursal">/ {{ sucursal.name }}</span>
                    </h4>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <div class="card p-3">
                        <p class="mb-0">Ventas</p>
                        <hr>
                        <p>Ventas Contado: <strong>${{ getSales(EFECTIVO).toLocaleString(2) }}</strong></p>
                        <p>Ventas Tarjeta: <strong>${{ getSales(CARD).toLocaleString(2) }}</strong></p>
                        <p>Cuenta Corriente: <strong>${{ getSales(CUENTA_CORRIENTE).toLocaleString(2) }}</strong></p>
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
                    <button type="button" class="btn btn-sm btn-primary" @click="getTotalTurnCash(closeDay = false)">Cerrar Turno</button>
                    <button type="button" class="btn btn-sm btn-primary" @click="getTotalTurnCash(closeDay = true)">Cerrar Día</button>
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
                <p>Ventas: $<strong>{{ totalTurnCash.toLocaleString(2) }}</strong></p>
                <p>Egresos: $<strong>{{ totalOutcomes.toLocaleString(2) }}</strong></p>
                <p>Ingresos: $<strong>{{ totalIncomes.toLocaleString(2) }}</strong></p>
                <hr>
                <p>Inicial: $<strong v-if="turn">{{ turn.start_cash.toLocaleString(2) }}</strong></p>
                <label class="mt-1">Caja Actual</label>
                <input @change="setTurnCorrection" v-model.number='actualCash' type="number" class="form-control">
                <label class="mt-1">Ajustar Caja</label>
                <input v-model.number='turnCorrection' type="number" class="form-control">
                <div v-if="closeDay" class='mt-2'>
                    <label class="mt-1">Z</label>
                    <input v-model.number='z' type="number" class="form-control">
                    <label class="mt-1">Guardar</label>
                    <input v-model.number='guardar' type="number" class="form-control">
                    <p class="mt-1">Diferencia: $<strong>{{ diference  }}</strong></p>
                </div>
            </div>
            <md-dialog-actions>
                <md-button class="md-primary" @click="showCloseTurnDialog = false">Cancelar</md-button>
                <md-button v-if="!closeDay" class="md-primary" @click="closeTurn">Enviar</md-button>
                <md-button v-if="closeDay" class="md-primary" @click="closeDayCashRegister">Enviar</md-button>
            </md-dialog-actions>
        </md-dialog>
    </div>
</template>
<script>
export default {
    name: "CashComponent",
    data() {
        return {
            EFECTIVO: 1,
            CARD: 2, 
            CUENTA_CORRIENTE: 3, 

            sucursal: null,
            cashRegister: null,
            turn: null,
            showAlert: false,
            alertTitle: '',
            alertContent: '',
            newMovementItem: {},
            showDialog: null,
            movementsType: ['Egreso', 'Ingreso'],

            actualCash: 0,

            showCloseTurnDialog: false,
            turnCorrection: 0,
            totalTurnCash: 0,
            totalTurnMovements: [],

            closeDay: false,
            z: 0,
            guardar: 0,
        }
    },
    computed: {
        diference() {
            return this.actualCash - this.guardar
        },
        outcomes() {
            return this.turn.movements != null ? this.turn.movements.filter(item => item.type == 0) : []
        },
        incomes() {
            return this.turn.movements != null ? this.turn.movements.filter(item => item.type == 1) : []
        },
        totalIncomes() {
            var total = 0
            this.totalTurnMovements.forEach(item => {
                if (item.type == 1)
                    total = item.total
            })
            return total
        },
        totalOutcomes() {
            var total = 0
            this.totalTurnMovements.forEach(item => {
                if (item.type == 0)
                    total = item.total
            })
            return total
        },
        totalSales() {
            return (this.getSales(1) + this.getSales(2)).toLocaleString(2)
        }
    },
    methods: {
        getData() {
            axios.get('/api/cash/cash_register').then(response => {
                this.sucursal = response.data.sucursal
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
            axios.post('/api/cash/movement', {id_turn: this.turn.id, movement: this.newMovementItem}).then(response => {
                if (response.data.statusCode === 200)
                    this.turn.movements.push(this.newMovementItem)
                this.newMovementItem = {}
                this.showDialog = false
                this.showAlert = true
                this.alertTitle = response.data.status
                this.alertContent = response.data.message
            });
        },
        showNewMovementForm($type) {
            this.showDialog = true
            this.newMovementItem.type = $type
        },
        getTotalTurnCash(closeTurn) {
            axios.get('/api/cash/turn/cash').then(response => {
                this.totalTurnCash = response.data.payments ? parseFloat(response.data.payments) : 0
                this.totalTurnMovements = response.data.movements
                this.showCloseTurnDialog = true
            });
        },
        closeTurn() {
            axios.put('/api/cash/turn/', { id_turn: this.turn.id, end_cash:  this.actualCash, correction: this.turnCorrection }).then(response => {
                this.showCloseTurnDialog = false
                this.showAlert = true
                this.alertTitle = response.data.status
                this.alertContent = response.data.message
                if (response.data.statusCode == 200)
                    this.turn = null
            }); 
        },
        closeDayCashRegister() {
            axios.put('/api/cash/cash_register/', { id_turn: this.turn.id, end_cash:  this.actualCash - this.guardar, correction: this.turnCorrection, z: this.z, guardar: this.guardar }).then(response => {
                this.showCloseTurnDialog = false
                this.showAlert = true
                this.alertTitle = response.data.status
                this.alertContent = response.data.message
                if (response.data.statusCode == 200) {
                    this.turn = null
                    this.cashRegister = null
                }
            }); 
        },
        getSales(type) {
            if (this.turn && this.turn.payments && this.turn.payments[type]) {
                return this.turn.payments[type]
            }
            return 0
        },
        setTurnCorrection() {
            this.turnCorrection = - (this.totalTurnCash + this.totalIncomes - this.totalOutcomes - this.actualCash + this.turn.start_cash) 
        }
    },
    created() {
        this.getData()
    }

}
</script>