<template>
  <div>
      <md-table v-model="searched" md-sort="name" md-sort-order="asc" md-card md-fixed-header @md-selected="onSelect">
        <md-table-toolbar>
          <div class="md-toolbar-section-start">
            <h1 class="md-title">Colores</h1>
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
          :md-description="`No se encontraron colores para '${search}'. Probá con otra palabra o crea un nuevo color.`">
          <md-button class="md-primary md-raised" @click="newColorDialogActive = true">Crear nuevo Color</md-button>
        </md-table-empty-state>

        <md-table-row slot="md-table-row" slot-scope="{ item }" md-selectable="multiple" md-auto-select>
          <md-table-cell md-label="Color" md-sort-by="name"><input @keydown.enter="editColor(item)" v-model="item.name" @change="editColor(item)" type="text"></md-table-cell>
          <md-table-cell><button @click="deleteSubmit(item)">Borrar</button></md-table-cell>
        </md-table-row>
    </md-table>


    <md-dialog-confirm
      :md-active.sync="activeConfirmDialog"
      md-title="¿Seguro querés borrar esos colores?"
      md-confirm-text="Dale mecha"
      md-cancel-text="Cancelar"
      @md-confirm="deleteSubmit" />

    <md-dialog-alert
      :md-active.sync="alertActive"
      :md-title="alert_title"
      :md-content="alert_content" />

    <md-dialog-prompt
      :md-active.sync="newColorDialogActive"
      v-model="newColor"
      md-title="¿Qué color querés agregar?"
      md-input-maxlength="255"
      md-input-placeholder="Ingresá el nombre del color..."
      md-confirm-text="Confirmar" 
      @md-confirm="sendNewColor" />
    
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
    name: 'StockColorsList',
    data() {
      return {
        search: null,
        searched: [],
        items: null,
        selected: [],
        activeConfirmDialog: false,
        newColorDialogActive: false,
        alertActive: false,
        alert_title: '',
        alert_content: '',
        newColor: null,
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
          plural = 'es'
        }

        return `${count} color${plural} seleccionado${plural}`
      },
      getData(){
        axios.get('/api/stock/colors').then(response => {
            this.items = response.data;
            this.searched = this.items
        });
      },
      searchOnTable () {
        this.searched = searchByName(this.items, this.search)
      },
      editColor(item) {
        if (item != null)
          axios.put('/api/stock/colors', {item: item})
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

        axios.delete('/api/stock/colors', {items: items})
        .then(response => {
            this.items = this.items.filter(item => !response.data.success.includes(item.id))
            this.searched = this.items
            this.alert_title = response.data.status
            this.alert_content = response.data.message
            this.alertActive = true
        });
      },
      sendNewColor() {
        if (this.newColor != null) {
          axios.post('/api/stock/colors', {name: this.newColor})
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