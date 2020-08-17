<template>
  <div>
      <stock-brand-selector :activeAddButton="false" v-on:updatebrand="updatebrand($event)"></stock-brand-selector>

      <md-table v-if="id_brand" v-model="searched" md-sort="name" md-sort-order="asc" md-card md-fixed-header @md-selected="onSelect">
        <md-table-toolbar>
          <div class="md-toolbar-section-start">
            <h1 class="md-title">Artículos</h1>
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
          <md-button class="md-primary md-raised" >Crear nuevo Artículo</md-button>
        </md-table-empty-state>

        <md-table-row slot="md-table-row" slot-scope="{ item }" md-selectable="multiple" md-auto-select>
          <md-table-cell md-label="Artículo" md-sort-by="code"><input type="text" v-model="item.code"></md-table-cell>
          <md-table-cell md-label="Color" md-sort-by="color">{{ item.color }}</md-table-cell>
          <md-table-cell md-label="Número" md-sort-by="number" md-numeric>{{ item.number }}</md-table-cell>
          <md-table-cell md-label="Precio Compra" md-sort-by="buy_price" md-numeric>${{ item.buy_price.toLocaleString() }}</md-table-cell>
          <md-table-cell md-label="Precio Venta" md-sort-by="sell_price" md-numeric>${{ item.sell_price.toLocaleString() }}</md-table-cell>
          <md-table-cell md-label="Stock" md-sort-by="stock" md-numeric>{{ item.stock }}</md-table-cell>
          <md-table-cell v-for="sucursal in sucursals" :key="sucursal.name" :md-label="sucursal.name"><span class="p-2" :class="getBackgroundClass(sucursal, item)">{{ getStockSucursal(sucursal, item) }}</span></md-table-cell>
        </md-table-row>
    </md-table>


    <md-dialog-confirm
      :md-active.sync="activeConfirmDialog"
      md-title="¿Seguro querés borrar esos artículos?"
      md-confirm-text="Dale mecha"
      md-cancel-text="Cancelar"
      @md-confirm="deleteSubmit" />

      <md-dialog-alert
      :md-active.sync="alertActive"
      :md-title="alert_title"
      :md-content="alert_content" />
    
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
    name: 'StockList',
    data() {
      return {
        search: null,
        searched: [],
        sucursals: null,
        items: null,
        selected: [],
        activeConfirmDialog: false,
        alertActive: false,
        alert_title: '',
        alert_content: '',
        id_brand: null
      }
    },
    props: {
        
    },
    methods: {
      onSelect (items) {
        this.selected = items
      },
      updatebrand(value) {
        this.id_brand = value.id
        if (this.id_brand != null)
          this.getData()
      },
      getAlternateLabel (count) {
        let plural = ''

        if (count > 1) {
          plural = 's'
        }

        return `${count} artículo${plural} seleccionado${plural}`
      },
      getSucursals(){
        axios.get('/api/sucursals').then(response => {
            this.sucursals = response.data;
        });
      },
      getBackgroundClass(sucursal, item) {
        for( var i = 0; i < item.items.length; i ++)
          if (item.items[i].id_sucursal == sucursal.id) {
            if (item.items[i].stock == 0)
              return 'red text-white'
            if (item.items[i].stock < 3)
              return 'orange'
            if (item.items[i].stock < 5)
              return 'yellow'
            return ''
          }
          return 'red text-white'
      },
      getStockSucursal(sucursal, item) {
        for( var i = 0; i < item.items.length; i ++)
          if (item.items[i].id_sucursal == sucursal.id)
            return item.items[i].stock
        return 0
      },
      getData(){
        axios.get('/api/stock/list', {params: { id_brand: this.id_brand }}).then(response => {
            this.items = response.data;
            this.searched = this.items
        });
      },
      searchOnTable () {
        this.searched = searchByBrandName(this.items, this.search)
      },
      deleteSubmit(){
          axios.delete('/api/stock/articles', {items: this.selected.map(
                                                  item => item.id
                                                  )})
          .then(response => {
              this.items = this.items.filter(item => !response.data.success.includes(item.id))
              this.searched = this.items
              this.alert_title = response.data.status
              this.alert_content = response.data.message
              this.alertActive = true
          });
      },
    },
    mounted() {
        this.getSucursals()
    }
  }
</script>
<style scoped>
  input {
    max-width: 100px;
  }
  .md-autocomplete.md-theme-default.md-autocomplete-box, th, td{
    max-width: 150px;
  }
</style>