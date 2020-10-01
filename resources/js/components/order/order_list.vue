<template>
    <div>
        <md-table v-model="searched" md-sort="first_name" md-sort-order="asc" md-card md-fixed-header @md-selected="onSelect">
            <md-table-toolbar>
                <div class="md-toolbar-section-start">
                    <h1 class="md-title">Ventas</h1>
                </div>

                <md-field md-clearable class="md-toolbar-section-end">
                    <md-input placeholder="Buscar..." v-model="search" @input="searchOnTable" />
                </md-field>
            </md-table-toolbar>

            <md-table-toolbar slot="md-table-alternate-header" slot-scope="{ count }">
                <div class="md-toolbar-section-start">{{ getAlternateLabel(count) }}</div>
                <div class="md-toolbar-section-end">
                    <md-button class="md-icon-button" @click="activeConfirmDialog = true">
                    <i class="far fa-trash-alt"></i>
                    </md-button>
                </div>
            </md-table-toolbar>

            <md-table-empty-state
                md-label="Ups, no hay coincidencias."
                :md-description="`No se encontraron ventas para '${search}'`">
            </md-table-empty-state>

            <md-table-row slot="md-table-row" slot-scope="{ item }" md-selectable="multiple" md-auto-select>
                <md-table-cell md-label="Nro" md-sort-by="id">{{ item.order_id }}</md-table-cell>
                <md-table-cell md-label="Tipo" md-sort-by="type">{{ item.type }}</md-table-cell>
                <md-table-cell md-label="Pares" md-sort-by="qty">{{ item.qty }}</md-table-cell>
                <md-table-cell md-label="Subtotal" md-sort-by="subtotal">${{ item.subtotal.toLocaleString(2) }}</md-table-cell>
                <md-table-cell md-label="Total" md-sort-by="total">${{ item.total.toLocaleString(2) }}</md-table-cell>
                <md-table-cell md-label="Precio Compra" md-sort-by="buy_price">${{ item.buy_price.toLocaleString(2) }}</md-table-cell>
                <md-table-cell><a class="btn btn-primary-outline btn-sm" :href="'/order/'+item.order_id">Detalle</a></md-table-cell>
            </md-table-row>

        </md-table>

        <div v-if="items && items.length > 0" class="navigation">
            <ul>
                <li><a @click="getData(prev_page)">Previous</a></li>
                <li><a @click="getData(next_page)">Next</a></li>
                <li><a @click="getData(last_page)">Last page</a></li>
            </ul>
        </div>

        <md-dialog-confirm
            :md-active.sync="activeConfirmDialog"
            md-title="¿Are you sure?"
            md-confirm-text="Just do it!"
            md-cancel-text="Cancelar"
            @md-confirm="deleteSubmit" />

        <md-snackbar md-position="center" :md-duration="5000" :md-active.sync="show_update_status" md-persistent>
            <span>{{ alert_content }}</span>
        </md-snackbar>
    </div>
</template>
<script>
const toLower = text => {
    return text.toString().toLowerCase()
}

const searchByBrandName = (items, term) => {
    if (term) {
        return items.filter(item => Object.values(item).some(val => String(val).toLocaleLowerCase().includes(term.toLocaleLowerCase())))
    }
    return items
}
export default {
    name: "OrderList",
    
    data() {
        return {
            prev_page: null,
            next_page: null, 
            last_page: null,
            search: null,
            searched: [],
            items: [],
            selected: [],
            show_update_status: false,
            alert_content: '',
            filters: {},
            filter_lines: [],
            activeConfirmDialog: false
        }
    },
    methods: {
        getPaymentDescription(payments) {
            console.log(payments)
        },
        getOrders(url){
            let endpoint = url == null ? '/api/contacts/get_contacts' : url
            axios.get('/api/order/', {params: { 
                                            filters: {date: '2020-08-18', id_sucursal: 1},
                                            type: 'sucursal'  }
                                            }
            ).then(response => {
                console.log(response.data)
                if (response.data.total == 0) {
                    this.items = [];
                }
                else {
                    this.items = response.data.data;
                    this.next_page = response.data.next_page_url
                    this.prev_page = response.data.prev_page_url
                    this.last_page = response.data.last_page_url
                }
                this.searched = this.items
            });
        },
        onSelect (items) {
            this.selected = items
        },
        getAlternateLabel (count) {
            let plural = ''

            if (count > 1) {
                plural = 's'
            }

            return `${count} venta${plural} seleccionada${plural}`
        },
        deleteSubmit() {
            console.log('asdasd')
        },
        searchOnTable () {
            this.searched = searchByBrandName(this.items, this.search)
        },
    },
    mounted() {
        this.getOrders()
    }
}
</script>