<template>
    <div class="form">
        <div class="row">
            <div class="col-md-5">
                <label>Codigo</label>
                <input type="text" v-model="barcode" @change="getDetailItem">
            </div>
            <div class="col-md-7" v-if='item'>
                <table class='table table-sm'>
                    <thead>
                        <tr>
                            <td>Marca</td>
                            <td>Artículo</td>
                            <td>Color</td>
                            <td>Número</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ item.brand_name }}</td>
                            <td>{{ item.code }}</td>
                            <td>{{ item.color }}</td>
                            <td>{{ item.number }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row">
            <div class="col-md-5">
                <label>Cantidad</label>
                <input type="number" v-model="qty">
            </div>
        </div>
        <button class="btn btn-sm btn-primary text-center" @click="generateLabel">Imprimir</button>

        <md-dialog-confirm
            :md-active.sync="alertActive"
            :md-title="alert_title"
            :md-content="alert_content"
            md-confirm-text="Imprimir Etiquetas"
            md-cancel-text="Cancelar"
            @md-confirm="printLabel" />

    </div> 
</template>
<script>
export default {
    name: "StockLabelComponent",
    data() {
        return {
            item: null,
            barcode: null,
            qty: 0,

            alertActive: false,
            alert_title: null,
            alert_content: null,
        }
    },
    methods: {
        getDetailItem() {
            if (this.barcode != null) {
                axios.get('/api/stock/detail_item_barcode', {params: { barcode: this.barcode }})
                .then(response => {
                    let data = response.data
                    this.barcode = null
                    if (data.item.length !== 0) {
                        this.item = response.data.item
                        return
                    }
                    else
                        this.item = null
                })
               // .finally(() => this.$refs.article.focus())
            }
        },
        generateLabel() {
            if (this.item && this.qty > 0)
                //TODO: formatear esto bien... Cambiar el controller del pdf para que sea generico y con buenas practicas
                this.item.stock_to_add = this.qty
                axios.post('/api/pdf/generate', {   items: [this.item],
                                                    color: this.item.color,
                                                    brand_name: this.item.brand_name,
                                                    code: this.item.code })
                .then(response => {
                    this.alert_title = response.data.status
                    this.alert_content = response.data.message
                    this.alertActive = true
                });
        },
        //TODO HACER API PYTHon
        printLabel() {

        }
    }
}
</script>