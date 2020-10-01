<template>
    <div>
        <div class="row" v-if="order">
            <div class="col-md-12">
                <div class="card p-3">
                    <div class="row">
                        <div class="col-md-6">
                            <p>Canal de Venta: <strong>Marketplace</strong></p>
                            <p>Vendedor: <strong>{{ order.seller }}</strong></p>
                            <p>Fecha: <strong>{{ getDate(order.created_at) }}</strong></p>
                            <p>Total: <strong>${{ order.total.toLocaleString(2) }}</strong></p>
                        </div>
                        <div class="col-md-6">
                            <p>Cliente: <strong>{{ order.orderable.client }}</strong></p>
                            <p>Dirección: <strong>{{ order.orderable.address }}</strong></p>
                            <p>Teléfono: <strong>{{ order.orderable.phone }}</strong></p>
                            <p>Zona: <strong>${{ order.orderable.zone }}</strong></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12 mt-3">
                <div class="card p-3">
                    <p>Comentarios: <strong>{{ order.orderable.comments }}</strong></p>
                </div>
            </div>

            <div class="mt-3 col-md-12">
                <div class="card p-3">
                    <h4>Items</h4>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th scope="col">Marca</th>
                                <th scope="col">Artículo</th>
                                <th scope="col">Color</th>
                                <th scope="col">Número</th>
                                <th scope="col">Precio Compra</th>
                                <th scope="col">Precio Venta</th>
                                <th scope="col">Total</th>
                                <th scope="col"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(i, index) in order.items" :key="index">
                                <th>{{ i.brand }}</th>
                                <td>{{ i.code }}</td>
                                <td>{{ i.color }}</td>
                                <td>{{ i.number }}</td>
                                <td>${{ i.buy_price }}</td>
                                <td>${{ i.sell_price }}</td>
                                <td>${{ i.total }}</td>
                                <td><button @click="activeChangeProccess(i)" class="btn btn-outline-primary btn-sm">Cambiar</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
        </div>
        
        <div>
            <button class="btn btn-danger" @click="showActiveDialog = true">Borrar</button>
        </div>

        <md-dialog-confirm
            :md-active.sync="showActiveDialog"
            md-title="¿Seguro querés borrar las venta?"
            md-confirm-text="Just do it!"
            md-cancel-text="Cancelar"
            @md-confirm="deleteSubmit" />
        
        <md-dialog-alert
			:md-active.sync="alertActive"
			:md-title="alertTitle"
			:md-content="alertContent" />

        <md-dialog-alert
			:md-active.sync="alertChangeActive"
			:md-title="alertChangeTitle"
			:md-content="alertChangeContent" />

        <md-dialog v-if="changeItem"
        :md-active.sync="showChangeItemDialog">
            <md-dialog-title>Cambiar {{ changeItem.brand }} {{ changeItem.code}} {{ changeItem.color }} Nro {{ changeItem.number }} </md-dialog-title>
            <md-content>
                <div class="p-2">
                    <div class="row mb-4">
                        <div class="col">
                            <stock-brand-selector :activeAddButton="false" :brand="brand" v-on:updatebrand="updatebrand($event)"></stock-brand-selector>
                        </div>
                        <div class="col">
                            <stock-article-selector :activeAddButton="false" :brand="brand" v-on:updatearticle="updatearticle($event)"></stock-article-selector>
                        </div>
                        <div class="col" v-if="article">
                            <stock-color-selector :activeAddButton="false" :article="article.id" v-if="brand && article" v-on:updatecolor="updatecolor($event)"></stock-color-selector>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <label>Número</label>
                            <input type="number" v-model.number="number">
                        </div>
                        <div class="col">
                            <button :disabled="!color || !article || !number || !brand" @click="getDetailItem()">Chequear Stock</button>
                        </div>
                    </div>
                    <div class="row" v-if="showDetailItemStock">
                        <div class="col">
                            {{ showStock }}
                        </div>
                    </div>
                </div>
            </md-content>
            <md-dialog-actions>
                <md-button class="md-primary" @click="showChangeItemDialog = false">Cancelar</md-button>
                <md-button v-if="shoeDetail" :disabled="shoeDetail.stock < 0" class="md-raised md-primary" @click.prevent="activeChangeProccessSecondStep()">Hacer Cambio</md-button>
            </md-dialog-actions>
        </md-dialog>

        <md-dialog v-if="changeItem && shoeDetail"
        :md-active.sync="showChangeItemSecondDialog">
            <md-dialog-title>Procesar Cambio</md-dialog-title>
            <md-content>
                <div class="p-2">
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <p>{{ changeItem.brand }} {{ changeItem.code}} {{ changeItem.color }} Nro {{ changeItem.number }}</p>
                            <p>por</p>
                            <p>{{ brand.name }} {{ article.code }} {{ color.name }} Nro {{ number }}</p>
                            <hr>
                            <p>Diferencia de precio: ${{ (shoeDetail.sell_price - changeItem.sell_price).toLocaleString(2) }}</p>
                            <label>Envío</label>
                            <input min="0" type="number" v-model.number="delivery">
                            <h4 class="mt-2" v-if="(delivery + shoeDetail.sell_price - changeItem.sell_price) >= 0">El cliente debe pagar ${{ (delivery + shoeDetail.sell_price - changeItem.sell_price).toLocaleString(2) }}</h4>
                            <h4 class="mt-2" v-if="(delivery + shoeDetail.sell_price - changeItem.sell_price) < 0">Hacer una nota de crédito por ${{ Math.abs(delivery + shoeDetail.sell_price - changeItem.sell_price).toLocaleString(2) }}</h4>
                        </div>
                    </div>
                </div>
            </md-content>
            <md-dialog-actions>
                <md-button class="md-primary" @click="showChangeItemSecondDialog = false">Cancelar</md-button>
                <md-button class="md-raised md-primary" @click.prevent="sendChangeRequest()">Confirmar</md-button>
            </md-dialog-actions>
        </md-dialog>

    </div>
</template>
<script>

const id = location.href.substring(location.href.lastIndexOf('/') + 1)

export default {
    name: "MarketplaceOrderDetailComponent",
    data() {
        return {
            order: null,
            showActiveDialog: false,
            alertActive: false,
            alertContent: "",
            alertTitle: "",

            alertChangeTitle: null,
            alertChangeContent: null,
            alertChangeActive: false,

            brand: null,
            article: null,
            color: null,
            number: null,
            showChangeItemDialog: false,
            changeItem: null,
            shoeDetail: null,
            showDetailItemStock: false,
            showChangeItemSecondDialog: false,
            delivery: 0
        }
    },
    computed: {
        showStock() {
            if (this.shoeDetail) 
                return `Hay ${this.shoeDetail.stock} zapatilla/s en stock`
            return "No hay stock"
            
        }
    },
    methods: {
        updatebrand: function(brand){
            this.brand = brand
            this.article = null
            this.color = null
            this.showDetailItemStock = false
        },
        updatearticle: function(article){
            this.article = article
            if (this.article != null && this.brand == null) {
                this.brand = { id: article.id_brand, name: article.brand_name }}
            this.color = null
            this.showDetailItemStock = false
        }, 
        updatecolor: function(color) {
            this.color = color
            this.showDetailItemStock = false
        },
        activeChangeProccess(item){
            this.showChangeItemDialog = true
            this.showDetailItemStock = false
            this.changeItem = item
        },
        activeChangeProccessSecondStep() {
            this.showChangeItemDialog = false
            this.showChangeItemSecondDialog = true
        },
        getOrder() {
            axios.get('/api/order/marketplace/detail/'+id)
            .then(response => {
                this.order = response.data
            })
        },
        getDetailItem() {
            axios.get('/api/stock/single_detail_item', { params: { id_shoe: this.article.id, id_color: this.color.id, number: this.number }})
            .then(response => {
                this.shoeDetail = response.data
                this.showDetailItemStock = true
            })
        },
        getDate(date) {
            let new_date = new Date(date)
            return new_date.getDate() + "/" + (new_date.getMonth()+1) + "/" + new_date.getFullYear()
        },
        deleteSubmit() {
            axios.delete('/api/order/marketplace/', { data: { orders: [ this.order.order_id ] }})
            .then(response => {
                this.showActiveDialog = false
                this.alertTitle = response.data.status
                this.alertContent = response.data.message
                this.alertActive = true
                if (response.data.statusCode == 200)
                    this.order = null
            })
        },
        sendChangeRequest() {
            var oldItem = this.changeItem
            var newItem = { id_shoe_detail: this.shoeDetail.id }
            
            axios.post('/api/order/marketplace/change', { data: { oldItem: oldItem, newItem: newItem, delivery: this.delivery }})
            .then(response => {
                this.showChangeItemSecondDialog = false
                this.alertChangeTitle = response.data.status
                this.alertChangeContent = response.data.message
                this.alertChangeActive = true
                if (response.data.statusCode == 200) {
                    this.alertChangeContent += response.data.comments
                }
                this.getOrder()  
            })

        }
    },
    mounted() {
        this.getOrder()
    }
}
</script>
<style scoped>
    .md-dialog {
        z-index: 6;
    }
</style>