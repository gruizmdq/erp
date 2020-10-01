<template>
    <div>
        <div class="row">
            <div class="col-md-4">
                <sucursal-selector @updatesucursal="updateSucursal($event)" label="Sucursal" :options="sucursals"></sucursal-selector>
            </div>
            <div class="col-md-4">
                <stock-brand-selector :activeAddButton="false" v-on:updatebrand="updateBrand($event)"></stock-brand-selector>
            </div>
            <div class="col-md-4" v-if="sucursal_selected">
                <button class="btn btn-sm btn-primary" @click="getStockInfo()">Empezar ajuste</button>                
            </div>
        </div>
        <hr>
        <div v-if="showStartProccess">
            <div class="row">
                <div class="col-md-12">
                    <h3>Total de pares en {{ getSucursalName }}{{ getBrandName }}: <strong>{{ actual_stock_total.toLocaleString(2) }}</strong></h3>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-3">
                    <label for="article">Artículo</label>
            		<input ref="article" type="text" class="form-control" v-model="barcode" @change="getDetailItem">
                    <button @keydown.tab="disableTab($event)" v-if="Object.values(items).length > 0" class="btn btn-sm btn-primary" @click="getPreview()">Previsualizar</button>
                    <a class="mt-3 white btn btn-sm btn-outline-primary" :href="'/stock/reset/processed?sucursal='+sucursal_selected" target="blank">Artículos Ajustados</a>
                    <a class="mt-1 white btn btn-sm btn-outline-primary" :href="'/stock/reset/unprocessed?sucursal='+sucursal_selected" target="blank">Faltantes de Ajustar</a>
                    <button class='btn btn-danger' @click="activeResetAllDialog = true">STOCK EN CERO</button>
                </div>
                <div class="col-md-5">
                    <div class="card p-2">
                        <p>Lecturas Correctas</p>
                        <table class="table" v-if="Object.values(items).length > 0">
                            <thead>
                                <tr>
                                    <td>Zapatilla</td>
                                    <td>Cantidad</td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="i in Object.values(items)" :key="'item-'+i.data.id">
                                    <td>{{ getItemDescription(i.data) }}</td>
                                    <td>{{ i.qty }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-2">
                        <p>Lecturas Erroneas</p>
                        <table class="table" v-if="Object.values(error_items).length > 0">
                            <thead>
                                <tr>
                                    <td>Código</td>
                                    <td>Cantidad</td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="i in Object.values(error_items)" :key="'erroritem-'+i.data">
                                    <td>{{ i.data.padStart(12, '0') }}</td>
                                    <td>{{ i.qty }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row mt-3" v-if="activeResult">
                <div class="col-md-4">
                    <div class="card p-2">
                        <p>Resultados Correctos</p>
                        <table class="table" v-if="result_success && result_success.length > 0">
                            <thead>
                                <tr>
                                    <td>Código</td>
                                    <td>Cantidad</td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="i in result_success" :key="'success-item-'+i.data.id">
                                    <td>{{ getItemDescription(i.data) }}</td>
                                    <td>{{ i.qty }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-2">
                        <p>Resultados con Errores</p>
                        <table class="table" v-if="result_errors && result_errors.length > 0">
                            <thead>
                                <tr>
                                    <td>Código</td>
                                    <td>Faltante</td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="i in result_errors" :key="'error-item-'+i.data.id">
                                    <td>{{ getItemDescription(i.data) }}</td>
                                    <td>{{ i.difference }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-md-4">
                    <button class='btn btn-primary' @click="sendResetRequest(true)">Ajustar TODO</button>
                    <button class='btn btn-primary' @click="sendResetRequest(false)">Ajustar solo Correctos</button>
                </div>
            </div>
        </div>

        <md-dialog :md-active.sync="showUpdateDialog">
            <md-dialog-title>Resultado</md-dialog-title>
            <div class="p-3" v-if='resultsUpdateErrors.length > 0'>
                <table class="table">
                    <thead>
                        <tr>
                            <td>Código</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="i in resultsUpdateErrors" :key="'error-update-item-'+i.id">
                            <td>{{ getItemDescription(i) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="p-3" v-else>
                <p>Se actualizaron todos los artículos correctamente</p>
            </div>
            <md-dialog-actions>
                <md-button class="md-primary" @click="showUpdateDialog = false">Ok</md-button>
            </md-dialog-actions>
        </md-dialog>

        <md-dialog :md-active.sync="activeResetAllDialog">
            <md-dialog-title>Poner stock en 0</md-dialog-title>
            <div class="p-3">
                <p>Todos los artículos que no hayan sido ajustados se pondrán en stock 0.</p>
            </div>
            <md-dialog-actions>
                <md-button class="md-primary" @click="activeResetAllDialog = false">Cancelar</md-button>
                <md-button class="md-raised md-accent" @click="sendResetAllRequest">Ok</md-button>
            </md-dialog-actions>
        </md-dialog>


        <md-dialog-alert
        :md-active.sync="activeResetAllResultAlert"
        :md-title="resultAllAlertTitle"
        :md-content="resultAllAlertMessage" />
    </div>
</template>
<script>
export default {
    name: "StockResetComponent",
    data() {
        return {
            sucursals: [],
            sucursal_selected: null,
            brand_selected: null,
            actual_stock_total: 0,

            showStartProccess: false,
            barcode: null,
            items: {},
            error_items: {},

            activeResult: false,
            result_success: [],
            result_errors: [],
            result_not_processed: [],

            resultsUpdateSuccess: [],
            resultsUpdateErrors: [],
            showUpdateDialog: false,

            activeResetAllDialog: false,
            activeResetAllResultAlert: false,
            resultAllAlertMessage: '',
            resultAllAlertTitle: '',
        }
    },
    computed: {
        getSucursalName() {
            if (this.sucursal_selected)
                for (var i = 0; i < this.sucursals.length; i++)
                    if (this.sucursals[i].id == this.sucursal_selected)
                    return this.sucursals[i].name
            return ""
        },
        getBrandName() {
            if (this.brand_selected)
                return " para la marca "+this.brand_selected.name
            return ""
        }
    },
    methods: {
        disableTab(e) {
            e.preventDefault()
        }, 
        getItemDescription(item) {
            return `${item.brand} - ${item.code} - ${item.color} - Nro ${item.number}`
        },
        appendItem(data){
            if (this.items[data.id])
                this.items[data.id].qty += 1
            else 
                this.items[data.id] = { data: data, qty: 1 }
        },
        appendError(barcode) {
            if (this.error_items[parseInt(barcode)])
                this.error_items[parseInt(barcode)].qty += 1
            else
                this.error_items[parseInt(barcode)] = { data: barcode, qty: 1}
        },
        updateSucursal(value){
            this.sucursal_selected = value
        },
        updateBrand(value){
            this.brand_selected = value
        },
        addSuccessResults(data){
            var result = []
            if (data.length > 0)
                data.forEach(item => {
                    result.unshift(this.items[item])
                })
            return result
        },
        addErrorResults(data){
            var result = []

            if (data.length > 0) {
                data.forEach(item => {
                    result.unshift({ data: this.items[item.id].data, difference: item.diff })
                })
            }
            return result
        },
        getPreview() {
            let brand_id = this.brand_selected ? this.brand_selected.id : null
            axios.get('/api/stock/reset/preview', { params: { sucursal: this.sucursal_selected, brand: brand_id, items: Object.values(this.items) }})
            .then(response => {
                this.result_success = this.addSuccessResults(response.data.success)
                this.result_errors = this.addErrorResults(response.data.errors)
                this.activeResult = true
            })
        },
        getDetailItem(event) {
            var barcode = event.target.value
            let brand_id = this.brand_selected ? this.brand_selected.id : null
            axios.get('/api/stock/reset/shoeDetail', { params: { sucursal: this.sucursal_selected, brand: brand_id, barcode: barcode }})
            .then(response => {
                if (response.data)
                    this.appendItem(response.data)
                else
                    this.appendError(barcode)
            })
            .finally(this.barcode = null);
        },
        getSucursals() {
            axios.get('/api/sucursals').then(response => {
                this.sucursals = response.data;
            });
        },
        getStockInfo() {
            this.resetResults()
            if (this.sucursal_selected) {
                let brand_id = this.brand_selected ? this.brand_selected.id : null
                axios.get('/api/stock/reset/stockInfo', { params: { sucursal: this.sucursal_selected, brand: brand_id }})
                .then(response => {
                    this.actual_stock_total = response.data
                    this.showStartProccess = true
                });
            }
        },
        sendResetRequest(withErrors) {
            var items = []
            items = this.result_success.map(
                    item => ({
                        id: item.data.id,
                        difference: 0
                }))
            
            if (withErrors)
                items = items.concat(this.result_errors.map(
                    item => ({
                        id: item.data.id,
                        difference: item.difference
                })))

            axios.put('/api/stock/reset/', { sucursal: this.sucursal_selected, data: items })
            .then(response => {
                this.resultsUpdateRequest(response.data)
                this.resetResults()
                this.items = {}
            });
        },
        sendResetAllRequest() {
            this.activeResetAllDialog = false
            axios.post('/api/stock/reset/all/', { sucursal: this.sucursal_selected })
            .then(response => {
                this.resultAllAlertMessage = response.data.message
                this.resultAllAlertTitle = response.data.status
                this.activeResetAllResultAlert = true
            });
        },
        resetResults() {
            this.activeResult = false
            this.result_success = {}
            this.result_errors = {}
            this.result_not_processed = {}
        },
        resultsUpdateRequest(data) {
            data.success.forEach(item => {
                this.resultsUpdateSuccess.unshift(this.items[item].data)
            })
            data.errors.forEach(item => {
                this.resultsUpdateErrors.unshift(this.items[item].data)
            })
            this.showUpdateDialog = true
        }
    },
    mounted() {
        this.getSucursals();
    }
}
</script>