<template>
	<div>
		<form class="text-center" ref="form" @submit="submitForm">
                <div class="form-row mb-4">
                    <div class="col">
                        <stock-brand-selector :brand="brand" v-on:updatebrand="updatebrand($event)"></stock-brand-selector>
                    </div>
                    <div class="col">
                        <stock-article-selector :brand="brand" v-on:updatearticle="updatearticle($event)"></stock-article-selector>
                    </div>
                    <div class="col">
                        <stock-color-selector v-if="brand && article" v-on:updatecolor="updatecolor($event)"></stock-color-selector>
                    </div>
                </div>

                <div class="form-row mb-4">
                    <div class="col-2">
                        <!-- First name -->
                        <input type="number" id="defaultRegisterFormFirstName" class="form-control"  v-model="numberFrom" placeholder="Desde" min='1' @change="getDetailItems">
                    </div>
                    <div class="col-2">
                        <!-- Last name -->
                        <input type="number" id="defaultRegisterFormLastName" class="form-control" v-model="numberTo" placeholder="Hasta" min='1' @change="getDetailItems">
                    </div>
                </div>

                <table class="table" v-if="color && brand && article && detailItems">
                    <thead>
                        <tr>
                        <th scope="col">Número</th>
                        <th scope="col">Stock Actual</th>
                        <th scope="col">Nuevos</th>
                        <th scope="col">Precio Compra</th>
                        <th scope="col">Precio Venta</th>
                        <th scope="col">Tienda Nube?</th>
                        <th scope="col">MarketPlace?</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td scope="col"> - </td>
                            <td scope="col"> - </td>
                            <td scope="col"><input min="0" type="number" v-model.number="new_stock_all" class="m-auto text-center form-control form-control-sm" style="max-width: 100px;"></td>
                            <td scope="col"><input v-model.number="new_buy_price_all" type="number" step="0.01" class="m-auto text-center form-control form-control-sm" style="max-width: 100px;"></td>
                            <td scope="col"><input v-model.number="new_sell_price_all" type="number" step="0.01" class="m-auto text-center form-control form-control-sm" style="max-width: 100px;"></td>
                            <td scope="col">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" v-model="tiendanube_all" id="tiendanube_all">
                                    <label class="custom-control-label" for="tiendanube_all"></label>
                                </div>
                            </td>
                            <td scope="col">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" v-model="marketplace_all"  id="marketplace_all">
                                    <label class="custom-control-label" for="marketplace_all"></label>
                                </div>
                            </td>
                        </tr>
                        <stock-item-row :stock="i.stock" :marketplace.sync="marketplace_all" :tiendanube.sync="tiendanube_all" :new_sell_price.sync="new_sell_price_all" :new_buy_price.sync="new_buy_price_all" :new_stock.sync="new_stock_all" v-for="i in detailItems" :key="i.number" :item.sync="i"> </stock-item-row>
                    </tbody>
                </table>

                <div class="text-center" v-if="color && brand && article && detailItems">
                    <a class="btn btn-outline-primary my-4" @click="reset">Reset</a>
                    <a class="btn btn-primary my-4" @click="activeConfirmDialog = true">Agregar</a>
                </div>
                </form>
                <md-dialog-confirm
                :md-active.sync="activeConfirmDialog"
                md-title="¿Está seguro de confirmar el stock?"
                md-confirm-text="Dale mecha"
                md-cancel-text="Cancelar"
                @md-confirm="confirmSubmit" />

                <md-dialog-alert
                :md-active.sync="alertActive"
                :md-title="alert_title"
                :md-content="alert_content" />
	</div>
</template>
<script>
	export default{
		data() {
			return {
                numberFrom: null,
                numberTo: null,
                brand: null,
                article: null,
                color: null, 
                detailItems: [],   
                new_stock_all: 0,
                new_buy_price_all: undefined,
                new_sell_price_all: undefined,
                marketplace_all: null,
                tiendanube_all: null,
                activeConfirmDialog: false,
                alertActive: false,
                alert_title: '',
                alert_content: ''
			}
        },
		methods: {
            updatebrand: function(brand){
                this.brand = brand
                this.article = null
                this.detailItems = []
            },
            updatearticle: function(article){
                if (this.brand == null) {
                    this.brand = { id: article.id_brand, name: article.brand_name }}
                this.article = article
                this.detailItems = []

            }, 
            updatecolor: function(color) {
                this.color = color
                this.detailItems = []
            },
            confirmSubmit() {
                if (this.$refs.form.checkValidity()) 
                    this.submitForm()
                else
                    this.$refs.form.reportValidity();
            },
            submitForm(){
                axios.post('/api/stock/update_items', {items: this.detailItems.map(
                                                        item => {
                                                            item.stock = item.stock_to_add
                                                            delete item.stock_to_add
                                                            return item
                                                        })})
                .then(response => {
                    this.detailItems = response.data.items
                    this.alert_title = response.data.status
                    this.alert_content = response.data.message
                    this.alertActive = true
                });
            },
            getDetailItems() {
                if (this.range(this.numberFrom, this.numberTo) < 0) {
                    this.detailItems = []
                    return
                }
                else {
                    axios.get('/api/stock/get_detail_item', {params: {id_shoe: this.article.id, id_color: this.color.id, from: this.numberFrom, to: this.numberTo}})
                    .then(response => {
                        var json = response.data
                        var items = []
                        for (var i = this.numberFrom; i <= this.numberTo; i++) {
                            if (json.length > 0 && json[0].number == i){
                                var obj = json.shift()
                                obj.stock_to_add = 0
                                items.push(obj)
                            }
                            else 
                                items.push({id_shoe: this.article.id, id_color: this.color.id, number: i, available_tiendanube: 0, available_marketplace: 0, sell_price: null, buy_price: null, stock: 0, stock_to_add: 0})
                        }
                        this.detailItems = items
                    });
                }
            },
            range(from, to) {
                if (from == null || to == null)
                    return -1
                return parseInt(to) +1 - parseInt(from)
            },
            reset(){
                this.detailItems = null
                this.numberTo = null
                this.numberFrom = null
            }
		}
	}
</script>