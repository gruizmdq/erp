<template>
  <div>
    <md-autocomplete 
      :value="selectedName" 
      :v-model="selectedName"
      :md-options="options" 
      @md-selected="getSelected" 
      @md-changed="getOptions"
      md-layout="box"
      >
      <label>Marca</label>
      <div class="">
          <md-button class="md-primary md-raised btn-sm" @click="activePrompt = true">Agregar</md-button>
      </div>
      <template slot="md-autocomplete-item" slot-scope="{ item }">{{ item.name }}</template>
    </md-autocomplete>


    <md-dialog-prompt
      :md-active.sync="activePrompt"
      v-model="new_brand_name"
      md-title="¿Qué marca querés agregar?"
      md-input-maxlength="255"
      md-input-placeholder="Nombre..."
      md-confirm-text="Enviar"
      @md-confirm="sendNewBrand" />

    <md-dialog-alert
      :md-active.sync="alert"
      :md-title="alert_title"
      :md-content="alert_content" />

    </div>
</template>

<script>
  export default {
    name: 'StockSelectorBrand',
    data() { 
      return{
        items: [],
        selected: this.brand,
        selectedName: '',
        options: [],
        activePrompt: false,
        new_brand_name: null,
        alert: false,
        alert_title: '',
        alert_content: ''
      }
    },
    props: {
      brand: {
        type: Object,
        default: null
      }
    },
    watch: {
      brand(newValue) {
        this.selected = newValue;
        if (this.selected != null)
          this.selectedName = this.selected.name
        else 
          this.selectedName = ''
      }
    },
    methods: {
        getOptions (searchTerm) {
            if (!searchTerm) {
              this.options = this.items
            } 
            else {
              const term = searchTerm.toLowerCase()
              this.options = this.items.filter(({ name }) => name.toLowerCase().includes(term))
            }
        },
        getSelected(term) {
            this.selected = term
            this.selectedName = term.name
            this.$emit('updatebrand', term)
        },
        sendNewBrand(value) {
          if (value != null) {
            this.new_brand_name = value
            axios.post('/api/new_brand', { name: value })
            .then(response => {
              if (response.data.statusCode == 200){
                this.alert_title = "¡Bien papá!"
                this.selected = value
                this.selectedName = value
                this.items.push(response.data.data)
                this.$emit('updatebrand', response.data.data)
              }
              else
                this.alert_title = "Ups, no se pudo che"
              this.alert_content = response.data.message
              this.alert = true
            })
            .catch((err) => {
                this.alert_title = "Ups, no se pudo che"
                this.alert_content = "Hubo un error al conectarse con el servidor."
                this.alert = true
            })
          }
        }
    },
    mounted() {
        axios.get('/api/get_brands').then(response => {
            this.items = response.data;
            this.options = response.data
        });
    }
  }
</script>

<style lang="scss" scoped>
  
</style>