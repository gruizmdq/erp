<template>
    <div>
        <div class="row">
            <div class="col">
                <stock-brand-selector :activeAddButton="false" :brand="brand" v-on:updatebrand="updatebrand($event)"></stock-brand-selector>
            </div>
            <div class="col">
                <stock-article-selector :activeAddButton="false" :brand="brand" v-on:updatearticle="updatearticle($event)"></stock-article-selector>
            </div>
        </div>
        <div v-if='items && items.length > 0'>
            <div class="row">
                <div class="col-md-12">
                    <table class="table">
                        <thead>
                            <tr>
                                <td>Número</td>
                                <td>Precio de Compra</td>
                                <td>Precio de Venta</td>
                                <td>% Marcado</td>
                                <td>Tienda Nube?</td>
                                <td>Market Place?</td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for='i in items' :key="i.number+i.sell_price">
                                <td>{{ i.number }}</td>
                                <td>{{ i.buy_price }}</td>
                                <td>{{ i.sell_price }}</td>
                                <td>{{ getPercentage(i.buy_price, i.sell_price).toLocaleString(2) }}%</td>
                                <td>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" :id="'tiendanube-checkbox-'+i.number+i.sell_price" v-model="i.available_tiendanube" disabled>
                                        <label class="custom-control-label" :for="'tiendanube-checkbox-'+i.number+i.sell_price"></label>
                                    </div>
                                </td>
                                <td>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" :id="'marketplace-checkbox-'+i.number+i.sell_price" v-model="i.available_marketplace" disabled>
                                        <label class="custom-control-label" :for="'marketplace-checkbox-'+i.number+i.sell_price"></label>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>            
            </div>
            <hr>
            <div class="row">
                <div class="col-md-12">
                    <h4>Modificar Precio</h4>
                    <div class="form-group">
                        <div class="col-md-3">
                            <label>Desde</label>
                            <input type="number" required v-model="from">
                        </div>
                        <div class="col-md-3 mt-2">
                            <label>Hasta</label>
                            <input type="number" required v-model="to">
                        </div>
                    </div>                
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 mt-2">
                    <div class='table-responsive text-nowrap'>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <td>Precio de Compra</td>
                                    <td>Precio de Venta</td>
                                    <td>% Marcado</td>
                                    <td>Tienda Nube?</td>
                                    <td>Market Place?</td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><input type="number" v-model="buy_price" required></td>
                                    <td><input type="number" v-model="sell_price" required></td>
                                    <td>{{ getProfit }}%</td>
                                    <td>
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="tiendanube-checkbox" v-model="available_tiendanube">
                                            <label class="custom-control-label" for="tiendanube-checkbox"></label>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="marketplace-checkbox" v-model="available_marketplace">
                                            <label class="custom-control-label" for="marketplace-checkbox"></label>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <button class="btn btn-sm btn-primary mt-2" @click="sendUpdate">Enviar</button>
        </div>

        <md-snackbar md-position="center" :md-duration="5000" :md-active.sync="showUpdateResponse" md-persistent>
            <span>{{ alert_title }} {{ alert_content }}</span>
        </md-snackbar>
    </div>
</template>
<script>
export default {
    name: "StockArticlesList",
    data() {
        return {
            brand: null,
            article: null, 
            items: null,
            sell_price: null,
            buy_price: null,
            available_marketplace: 0, 
            available_tiendanube: 0,
            from: null,
            to: null,

            showUpdateResponse: false,
            alert_content: null,
            alert_title: null
        }
    },
    computed: {
        getProfit() {
            if (this.sell_price && this.buy_price > 0)
                return (this.sell_price / this.buy_price * 100).toLocaleString(2)
            return 0
        }
    },
    methods: {
        updatebrand: function(brand){
            this.brand = brand
            this.article = null
            this.items = null
        },
        updatearticle: function(article){
            if (this.brand == null) {
                this.brand = { id: article.id_brand, name: article.brand_name }}
            this.article = article
            if (this.article != null)
                this.getDetailItems()
        },
        getPercentage(buy, sell) {
            return sell / buy * 100
        },
        getDetailItems() {
            axios.get('/api/stock/article/items', { params: { id_shoe: this.article.id } })
            .then(response => {
                if (response.status === 200) {
                    this.items = response.data
                } 
                else
                    this.items = null
            });
        },
        sendUpdate() {
            if (this.checkFromTo) {
                axios.put('/api/stock/article/items', { id_shoe: this.article.id, values: this.getObject() } )
                .then(response => {
                    this.alert_content = response.data.message
                    this.alert_title = response.data.status
                    this.showUpdateResponse = true
                    this.reset()
                    if (response.data.statusCode === 200)
                        this.getDetailItems()
                });
            }
        },
        checkFromTo() {
            return (this.from && this.to && this.to >= this.from && this.from > 0)
        },
        getObject() {
            return {
                sell_price: this.sell_price,
                buy_price: this.buy_price,
                available_marketplace: this.available_marketplace, 
                available_tiendanube: this.available_tiendanube,
                from: this.from,
                to: this.to,
            }
        },
        reset() {
            this.from = null,
            this.to = null,
            this.sell_price = null,
            this.buy_price = null,
            this.available_tiendanube = 0,
            this.available_marketplace = 0
        }
        
    }
}
</script>