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
            <stock-brand-selector :activeAddButton="false" :brand="brand" v-on:updatebrand="updatebrand($event)"></stock-brand-selector>
        </div>
        <div class="col">
            <stock-article-selector :activeAddButton="false" :brand="brand" v-on:updatearticle="updatearticle($event)"></stock-article-selector>
        </div>
        <div class="col">
            <stock-color-selector :activeAddButton="false" v-if="brand && article" v-on:updatecolor="updatecolor($event)"></stock-color-selector>
        </div>
      </div>

      <div class="form-row mb-4">
        <div class="col-2">
            <input type="number" class="z-depth-1 form-control"  v-model="numberFrom" placeholder="Desde" min='1' @change="getDetailItems">
        </div>
        <div class="col-2">
            <input type="number" class="z-depth-1 form-control" v-model="numberTo" placeholder="Hasta" min='1' @change="getDetailItems">
        </div>
      </div>
      <table class="table z-depth-1 w-100 white" v-if="color && brand && article && detailItems">
        <thead>
            <tr>
              <th scope="col">Número</th>
              <th v-for="s in sucursals" :key="s" scope="col">Stock {{ s.name }}</th>
              <th scope="col">Desde</th>
              <th scope="col">Hasta</th>
              <th scope="col">Cantidad</th>
            </tr>
        </thead>
        <tbody>
            <stock-movement-item v-for="i in detailItems" :key="i" :item.sync="i" :sucursals="sucursals"> </stock-movement-item>
        </tbody>
      </table>
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
        alertActive: false,
        alert_title: '',
        alert_content: '',

        brand: null,
        article: null, 
        color: null, 
        numberFrom: null,
        numberTo: null,
        detailItems: [],
        sucursals: []
      }
    },
    props: {
        
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
      onSelect (items) {
        this.selected = items
      },
      getDetailItems() {
        if (this.range(this.numberFrom, this.numberTo) < 0) {
            this.detailItems = []
            return
        }
        else {
            axios.get('/api/stock/get_detail_item', {params: {id_shoe: this.article.id, id_color: this.color.id, from: this.numberFrom, to: this.numberTo, get_sucursal_items: true}})
            .then(response => {
                this.detailItems = response.data
                console.log(response.data)
            });
        }
      },
      range(from, to) {
          if (from == null || to == null)
              return -1
          return parseInt(to) +1 - parseInt(from)
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
    },
    mounted() {
        this.getData();
        this.getSucursals();
    }
  }
</script>