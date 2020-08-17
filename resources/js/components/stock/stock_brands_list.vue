<template>
  <div>
      <md-table v-model="searched" md-sort="name" md-sort-order="asc" md-card md-fixed-header @md-selected="onSelect">
        <md-table-toolbar>
          <div class="md-toolbar-section-start">
            <h1 class="md-title">Marcas</h1>
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
          :md-description="`No se encontraron marcas para '${search}'. Probá con otra palabra o crea una nueva marca.`">
          <md-button class="md-primary md-raised" @click="newBrandDialogActive = true">Crear nueva Marca</md-button>
        </md-table-empty-state>

        <md-table-row slot="md-table-row" slot-scope="{ item }" md-selectable="multiple" md-auto-select>
          <md-table-cell md-label="Marca" md-sort-by="name"><input @keydown.enter="editBrand(item)" v-model="item.name" @change="editBrand(item)" type="text"></md-table-cell>
          <md-table-cell><button @click="deleteSubmit(item)">Borrar</button></md-table-cell>
        </md-table-row>
    </md-table>


    <md-dialog-confirm
      :md-active.sync="activeConfirmDialog"
      md-title="¿Seguro querés borrar esas marcas?"
      md-confirm-text="Dale mecha"
      md-cancel-text="Cancelar"
      @md-confirm="deleteSubmit" />

    <md-dialog-alert
      :md-active.sync="alertActive"
      :md-title="alert_title"
      :md-content="alert_content" />

    <md-dialog-prompt
      :md-active.sync="newBrandDialogActive"
      v-model="newBrand"
      md-title="¿Qué marca querés agregar?"
      md-input-maxlength="255"
      md-input-placeholder="Ingresá el nombre de la marca..."
      md-confirm-text="Confirmar" 
      @md-confirm="sendNewBrand" />
    
    <md-snackbar md-position="center" :md-duration="5000" :md-active.sync="showEditResponse" md-persistent>
      <span>{{ alert_content }}</span>
    </md-snackbar>

  </div>
</template>

<script>
  const toLower = text => {
    return text.toString().toLowerCase()
  }

  const searchByName = (items, term) => {
    if (term) {
      return items.filter(item => Object.values(item).some(val => String(val).toLocaleLowerCase().includes(term.toLocaleLowerCase())))
    }
    return items
  }

  export default {
    name: 'StockBrandsList',
    data() {
      return {
        search: null,
        searched: [],
        items: null,
        selected: [],
        activeConfirmDialog: false,
        newBrandDialogActive: false,
        alertActive: false,
        alert_title: '',
        alert_content: '',
        newBrand: null,
        showEditResponse: false
      }
    },
    props: {
        
    },
    methods: {
      onSelect (items) {
        this.selected = items
      },
      getAlternateLabel (count) {
        let plural = ''

        if (count > 1) {
          plural = 's'
        }

        return `${count} marca${plural} seleccionada${plural}`
      },
      getData(){
        axios.get('/api/stock/get_brands').then(response => {
            this.items = response.data;
            this.searched = this.items
        });
      },
      searchOnTable () {
        this.searched = searchByName(this.items, this.search)
      },
      editBrand(item) {
        if (item != null)
          axios.post('/api/stock/edit_brand', {item: item})
          .then(response => {
            this.alert_content = response.data.message
            this.showEditResponse = true
          });
      },
      deleteSubmit(item){
        let items = []
        if (item != null)
          items.push(item.id)
        else
          items = this.selected.map(item => item.id)

        axios.delete('/api/stock/brands', {items: items})
        .then(response => {
            this.items = this.items.filter(item => !response.data.success.includes(item.id))
            this.searched = this.items
            this.alert_title = response.data.status
            this.alert_content = response.data.message
            this.alertActive = true
        });
      },
      sendNewBrand() {
        if (this.newBrand != null) {
          axios.post('/api/stock/brands', {name: this.newBrand})
          .then(response => {
              this.items.push(response.data.data)
              this.searched = this.items
              this.alert_title = response.data.status
              this.alert_content = response.data.message
              this.alertActive = true
          });
        }
      }
    },
    mounted() {
        this.getData()
    }
  }
</script>