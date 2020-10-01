<template>
    <div>
        <md-table v-model="searched" md-sort="id" md-sort-order="asc" md-card md-fixed-header @md-selected="onSelect">
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
                <md-table-cell md-label="Nro" md-sort-by="id">{{ item.id }}</md-table-cell>
                <md-table-cell md-label="Fecha" md-sort-by="created_at">{{ getDate(item.created_at) }}</md-table-cell>
                <md-table-cell md-label="Vendedor" md-sort-by="seller">{{ item.seller }}</md-table-cell>
                <md-table-cell md-label="Cliente" md-sort-by="client">{{ item.client }}</md-table-cell>
                <md-table-cell md-label="Teléfono" md-sort-by="phone">{{ item.phone }}</md-table-cell>
                <md-table-cell md-label="Cantidad" md-sort-by="qty">{{ item.qty }}</md-table-cell>
                <md-table-cell md-label="Total" md-sort-by="total">${{ item.total.toLocaleString(2) }}</md-table-cell>
                <md-table-cell><a class="btn btn-primary-outline btn-sm" :href="'/marketplace/order/'+item.id">Detalle</a></md-table-cell>
            </md-table-row>

        </md-table>

        <div v-if="items && items.length > 0" class="navigation">
            <ul class="mt-2 pagination pg-blue">
                <li class="white page-item"><a class="page-link" @click="getOrders(prev_page)">Anterior</a></li>
                <li class="white page-item"><a class="page-link" @click="getOrders(next_page)">Siguiente</a></li>
                <li class="white page-item"><a class="page-link" @click="getOrders(last_page)">Última página</a></li>
            </ul>
        </div>

        <md-dialog-confirm
            :md-active.sync="activeConfirmDialog"
            md-title="¿Seguro querés borrar las ventas?"
            md-confirm-text="Just do it!"
            md-cancel-text="Cancelar"
            @md-confirm="deleteSubmit" />

        <md-dialog-alert
			:md-active.sync="alertActive"
			:md-title="alertTitle"
			:md-content="alertContent" />

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
    name: "MarketplaceOrdersComponent",
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
            activeConfirmDialog: false,
            alertActive: false,
            alertContent: "",
            alertTitle: "",
        }
    },
    methods: {
        getOrders(url){
            let endpoint = url != null ? url : '/api/order/marketplace/orders'
            axios.get(endpoint)
            .then(response => {
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
            axios.delete('/api/order/marketplace/', { data: { orders: this.selected.map(item => item.id) }})
            .then(response => {
                this.activeConfirmDialog = false
                this.alertTitle = response.data.status
                this.alertContent = response.data.message
                this.alertActive = true
                if (response.data.statusCode == 200) {
                    this.selected = []
                    this.getOrders()
                }
            })
        },
        searchOnTable () {
            this.searched = searchByBrandName(this.items, this.search)
        },
        getDate(date) {
            let new_date = new Date(date)
            return new_date.getDate() + "/" + (new_date.getMonth()+1) + "/" + new_date.getFullYear()
        }
    },
    mounted() {
        this.getOrders()
    }
}
</script>