<template>
    <div>
        <div class="form-row mb-4">
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
        <div class="row" v-if="numbers && numbers.length > 0">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <table class="table">
                        <thead>
                            <tr>
                                <td>Número</td>
                                <td>Stock</td>
                                <td>Precio</td>
                                <td></td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="n in numbers" :key="n.number">
                                <td>{{ n.number }}</td>
                                <td>{{ n.stock }}</td>
                                <td>${{ n.sell_price.toLocaleString(2) }}</td>
                                <td><button @click="addItem(n)" :disabled="n.stock == 0" class="btn btn-sm btn-primary">Agregar</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="row mt-3" v-if="detailItems && detailItems.length > 0">
            <div class="col-md-12">
                <div class="card p-3">
                    <h4>Artículos</h4>
                    <table class="table">
                        <thead>
                            <tr>
                                <td>Descripción</td>
                                <td>Precio</td>
                                <td></td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(detail, index) in detailItems" :key="'detail-'+index">
                                <td>{{ getDescription(detail) }}</td>
                                <td>${{ detail.sell_price.toLocaleString(2) }}</td>
                                <td><button v-on:click.prevent="deleteObject(index)" class="btn btn-sm btn-danger">Eliminar</button></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>Total</td>
                                <td>${{ getTotal().toLocaleString(2) }}
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        <hr>
        <div class="row mt-3" v-if="detailItems && detailItems.length > 0">
            <div class="col-md-12">
                <div class="card p-3">
                    <div class="form-row mb-4">
                        <div class="col">
                            <label>Nombre</label>
                            <input required v-model="client" type="text" class="form-control">
                            <small v-if="!client" class="form-text mb-4 red-text">
                                Completar
                            </small>
                        </div>
                        <div class="col">
                            <label>Teléfono</label>
                            <input required v-model="phone" type="text" class="form-control">
                            <small v-if="!phone" class="form-text mb-4 red-text">
                                Completar
                            </small>
                        </div>
                        <div class="col">
                            <label>Dirección</label>
                            <input required v-model="address" type="text" class="form-control">
                            <small v-if="!address" class="form-text mb-4 red-text">
                                Completar
                            </small>
                        </div>
                    </div>
                    <div class="form-row mb-4">
                        <div class="col">
                            <label>Zona</label>
                            <input required v-model="zone" type="text" class="form-control">
                            <small v-if="!zone" class="form-text mb-4 red-text">
                                Completar
                            </small>
                        </div>
                        <div class="col">
                            <label>Costo de Envío</label>
                            <input required v-model.number="delivery" type="number" class="form-control">
                        </div>
                    </div>
                    <label>Comentarios</label>
                    <textarea v-model="comments" cols="30" rows="4"></textarea>
                    <div>
                        <button :disabled="!client || !address || !zone || !phone" @click="confirmDialogActive = true" class="btn btn-primary">Confirmar</button>
                    </div>
                </div>
            </div>
        </div>

        <md-dialog
        :md-active.sync="confirmDialogActive">
            <md-dialog-title>¿Seguro que desea confirmar la venta?</md-dialog-title>
            <md-content>
                <div class="p-3">
                    <p>Total de Pares: {{ detailItems.length }}</p>
                    <p>Precio: ${{ getTotal().toLocaleString(2) }}
                    </p>
                    <p>Envío: ${{ delivery.toLocaleString(2) }}</p>
                    <hr>
                    <h3>Total: ${{ (getTotal() + delivery).toLocaleString(2) }}</h3>
                </div>
            </md-content>
            <md-dialog-actions>
                <md-button class="md-primary" @click="confirmDialogActive = false">Cancelar</md-button>
                <md-button class="md-raised md-primary" @click.prevent="sendOrder">Confirmar</md-button>
            </md-dialog-actions>
        </md-dialog>

        <md-dialog-alert
			:md-active.sync="alertActive"
			:md-title="alertTitle"
			:md-content="alertContent" />
    </div>
</template>
<script>
export default {
    name: "MarketplaceNewOrderComponent",
    data() {
        return {
            brand: null,
            article: null,
            color: null,
            detailItems: [],
            numbers: [],
            confirmDialogActive: false,

            client: null,
            address: null,
            phone: null,
            delivery: 0,
            zone: null,
            comments: null,

            alertActive: false,
            alertTitle: null,
            alertContent: null
        }
    },
    methods: {
        updatebrand: function(brand){
            this.brand = brand
            this.article = null
            this.color = null
            this.numbers = []
        },
        updatearticle: function(article){
            if (this.brand == null) {
                this.brand = { id: article.id_brand, name: article.brand_name }}
            this.article = article
            this.color = null
            this.numbers = []
        }, 
        updatecolor: function(color) {
            this.color = color
            this.numbers = []
            this.getNumbers()
        },
        deleteObject: function(index) {
            this.$delete(this.detailItems, index);
        },
        getDescription(detail) {
            return `${detail.brand.name} - ${detail.article.code} - ${detail.color.name} Nro ${detail.number}`
        },
        getNumbers() {
            axios.get('/api/stock/numbers', { params: { id_article: this.article.id, id_color: this.color.id }})
            .then(response => {
              this.numbers = response.data
          });
        },
        getTotal() {
            return this.detailItems.reduce(function(a, b){
                                    return a + b.sell_price;
                                }, 0)
        },
        addItem(item) {
            for (var i = 0; i < this.numbers.length; i++) {
                if (this.numbers[i].number == item.number) {
                    this.numbers[i].stock --
                    break
                }
            }
            this.detailItems.push({
                brand: this.brand, 
                article: this.article,
                color: this.color,
                number: item.number,
                sell_price: item.sell_price
            })
        },
        sendOrder() {
            axios.post('/api/order/marketplace', { data: this.createRequest() })
            .then(response => {
                this.confirmDialogActive = false
                this.alertTitle = response.data.status
                this.alertContent = response.data.message
                if (response.data.results.length > 0)
                    response.data.results.forEach(element => {
                        this.alertContent += "<br><br>"+element.shoe+" => "+element.sucursal
                    });
                this.alertActive = true
                this.resetOrder()
            })
        },
        createRequest() {
            let data = {}
            data.items = this.detailItems.map(item => ({
                    id: item.article.id,
                    color: item.color.id,
                    number: item.number,
					qty: 1,
					sell_price: item.sell_price,
                }))
            data.client = this.client
            data.phone = this.phone
            data.address = this.address
            data.zone = this.zone
            data.comments = this.zone
            data.delivery = this.delivery
            data.total = this.getTotal()
            data.subtotal = this.getTotal()
            return data
        },
        resetOrder() {
            this.color = null
            this.article = null
            this.brand = null
            this.detailItems = []
            this.numbers = []

            this.client = null
            this.address = null
            this.phone = null
            this.delivery = 0
            this.zone = null
            this.comments = null
        }
    },
}
</script>