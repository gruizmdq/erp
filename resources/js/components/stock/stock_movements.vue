<template>
  <div>
    <div class="row wow fadeIn">
        <div class="col-md-12 mb-4">
          <md-table v-model="searched" md-sort="name" md-sort-order="asc" md-card md-fixed-header @md-selected="onSelect">
            <md-table-toolbar>
              <div class="md-toolbar-section-start">
                <h1 class="md-title">Movimientos</h1>
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
              md-label="Ups, no hay coincidencia"
              :md-description="`No se encontraron artículos para '${search}'. Probá con otra palabra o crea un nuevo artículo.`">
              <md-button class="md-primary md-raised" >Crear nuevo movimiento</md-button>
            </md-table-empty-state>

            <md-table-row slot="md-table-row" slot-scope="{ item }" md-selectable="multiple" md-auto-select>
              <md-table-cell md-label="Marca" md-sort-by="name">{{ item.brand_name }}</md-table-cell>
              <md-table-cell md-label="Artículo" md-sort-by="articulo">{{ item.code }}</md-table-cell>
              <md-table-cell md-label="Color" md-sort-by="color">{{ item.color }}</md-table-cell>
              <md-table-cell md-label="Número" md-sort-by="number" md-numeric>{{ item.number }}</md-table-cell>
              <md-table-cell md-label="Desde" md-sort-by="desde">{{ item.sucursal_from_name }}</md-table-cell>
              <md-table-cell md-label="Hasta" md-sort-by="hasta">{{ item.sucursal_to_name }}</md-table-cell>
              <md-table-cell md-label="Fecha" md-sort-by="fecha">{{ getDateFormat(item.created_at) }}</md-table-cell>
            </md-table-row>
        </md-table>
      </div>
    </div>


    <div class="card mb-4 wow fadeIn">
      <div class="card-body d-sm-flex justify-content-between">
        <h4 class="mb-2 mb-sm-0 pt-1">
          <span>Agregar movimiento</span>
        </h4>
      </div>
    </div>

    <form class="wow fadeIn" action="">
      <div class="form-row mb-4">
        <div class="col">
            <input type="text" class="z-depth-1 form-control"  v-model="barcode" placeholder="Código de barras" @change="getDetailItem">
        </div>
        <div class="col">
            <sucursal-selector label="Desde" :options="sucursals"></sucursal-selector>
        </div>
        <div class="col">
            <sucursal-selector label="Hasta" :options="sucursals"></sucursal-selector>
        </div>
      </div>

      <table class="table z-depth-1 w-100 white" v-if="detailItems.length > 0">
        <thead>
            <tr>
              <th scope="col">Marca</th>
              <th scope="col">Artículo</th>
              <th scope="col">Color</th>
              <th scope="col">Número</th>
              <th v-for="s in sucursals" :key="s+Math.random()" scope="col">Stock {{ s.name }}</th>
              <th scope="col">Cantidad</th>
            </tr>
        </thead>
        <tbody>
            <stock-movement-item v-for="i in detailItems" :key="i.id + Math.random()" :item.sync="i" :sucursals="sucursals"> </stock-movement-item>
        </tbody>
      </table>

      <div v-if="detailItems.length > 0" class="md-toolbar-section-end">
          <button type="button" class="btn btn-primary" @click="activeConfirmDialog = true">
            Confirmar
          </button>
      </div>

      <md-dialog-confirm
      :md-active.sync="activeConfirmDialog"
      md-title="¿Seguro querés confirmar movimientos de stock?"
      md-confirm-text="Dale mecha"
      md-cancel-text="Cancelar"
      @md-confirm="confirmSubmit" />

      <md-dialog-alert
      :md-active.sync="alertActive"
      :md-title="alert_title"
      :md-content="alert_content" />

      <md-dialog-confirm
      :md-active.sync="alertActivePrint"
      :md-title="alert_title"
      :md-content="alert_content"
      md-confirm-text="Imprimir"
      md-cancel-text="Cancelar"
      @md-confirm="confirmPrintMovements" />

    </form>
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
    name: 'StockMovements',
    data() {
      return {
        data: null,
        searched: [],
        search: null,
        activeConfirmDialog: false,
        alertActivePrint: false,
        alertActive: false,
        alert_title: '',
        alert_content: '',

        barcode: null,
        detailItems: [],
        sucursals: [],

      }
    },
    props: {
        
    },
    methods: {
      onSelect (items) {
        this.selected = items
      },
      getDetailItem() {
        if (this.barcode != null) {
            axios.get('/api/stock/get_detail_item_barcode', {params: { barcode: this.barcode }})
            .then(response => {
              let data = response.data
              this.barcode = null
              if (data.item.length === 0) {
                this.alertActive = true;
                this.alert_title =  data.title
                this.alert_content = data.message
                return
              }
              // check if article already have one movement
              var index = 0;
              this.detailItems.forEach(i => {
                if (i.id === data.item.id){
                  i.qty += 1
                  return
                }    
                index++;
              });
              if (index === this.detailItems.length) {
                data.item.qty = 1
                this.detailItems.unshift(data.item)
              }
            });
        }
      },
      getDateFormat(date) {
        date = new Date(date)
        return date.getDate() + "/" + date.getMonth() + "/" + date.getFullYear()
      },
      getAlternateLabel (count) {
        let plural = ''

        if (count > 1) {
          plural = 's'
        }

        return `${count} movimiento${plural} seleccionado${plural}`
      },
      searchOnTable () {
        this.searched = searchByBrandName(this.data, this.search)
      },
      getData(){
        axios.get('/api/stock/get_movements').then(response => {
            this.data = response.data;
            this.searched = this.data
        });
      },
      getSucursals() {
        axios.get('/api/get_sucursals').then(response => {
            this.sucursals = response.data;
        });
      },
      confirmSubmit() {
        axios.post('/api/stock/add_movements', {items: this.detailItems.map(item => {
                                                        return {
                                                          id: item.id, 
                                                          qty: item.qty,
                                                        }
                                                      }),
                                                from: sucursalFrom, 
                                                to: sucursalTo })
        .then(response => {
          this.alert_title = response.data.status
          this.alert_content = response.data.message
          this.alertPrintActive = true
        })
      },
      confirmPrintMovements () {
        console.log('jeje')
      }
    },
    created() {
        this.getData();
        this.getSucursals();
    }
  }
</script>