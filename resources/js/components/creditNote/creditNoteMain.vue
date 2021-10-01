<template>
    <div>
        <div class="row">
            <div class="col">
                <h4>Ingresar artículos que se devuelven</h4>
            </div>
        </div>
        <div class="form-row mb-4 mt-4">
            <div class="col-3">
                <div>
                    <label for="article">Artículo</label>
                    <input ref="articleForChangeInput" id="articleForChangeInput" type="text" class="form-control"  v-model="barcode" @change="getDetailItem(barcode)">
                </div>
            </div>
        </div>
        <hr />

        <table class="table" v-if="itemsForChangeList.length">
            <thead>
                <tr>
                    <th scope="col">Código</th>
                    <th scope="col">Descripción</th>
                    <th scope="col">Precio</th>
                    <th scope="col"></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(item, index) in itemsForChangeList" :key="'item-change-'+index">
                    <td>{{ item.barcode }}</td>
                    <td>{{ getDescription(item) }}</td>
                    <td>${{ item.sell_price.toLocaleString(2) }}</td>
                    <td><button type="button" class="boton" @click="removeItemForChange(item, index)">Borrar</button></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td>${{ subtotalForChange.toLocaleString(2) }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <div class="row">
            <hr>
            <div class="col">
                <p>Total {{ itemsForChangeList.length }}</p>
                <input :disabled="itemsForChangeList.length > 0" type="number" v-model="subtotalForChange">
            </div>
        </div>
        <div class="row mt-3">
            <div class="col">
                <button type="button" @click="sendCreateCreditNoteRequest">Crear Nota de Crédito</button>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    name: "CreditNoteMain",
    data() {
        return {
            creditNote: null,
            amount: null,
            itemsForChangeList: [],
            barcode: null,
            subtotalForChange: 0,
        }
    },
    methods: {
        getDescription(item) {
            return `${item.brand_name} - ${item.code} - ${item.color} - Nro: ${item.number}`.toUpperCase()
        },
        getDetailItem(barcode) {
            if (barcode != null) {
                axios.get('/api/stock/detail_item_barcode', {params: { barcode: barcode }})
                .then(response => {
                    let data = response.data
                    this.barcode = null
                    if (data.item.length !== 0) {
                        if (!this.itemsForChangeList.length) {
                            this.subtotalForChange = parseFloat(data.item.sell_price)
                        }
                        else {
                            this.subtotalForChange += parseFloat(data.item.sell_price)
                        }
                        this.itemsForChangeList.push(data.item)
                    }
                })
                .finally(() => {
                    this.$refs.articleForChangeInput.focus()
                })
            }
        },
        removeItemForChange(item, index) {
            this.itemsForChangeList.splice(index, 1)
            this.subtotalForChange -= parseFloat(item.sell_price)
        },
        sendCreateCreditNoteRequest() {
            axios.post('/api/creditNote/createWithoutOrder', { data: { 
                amount: this.subtotalForChange,
                items: this.itemsForChangeList
            } })
            .then(response => {
                console.log(response.data)
            })
        }
    },

}
</script>