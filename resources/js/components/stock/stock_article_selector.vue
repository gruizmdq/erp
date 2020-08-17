<template>
  <div>
    <md-autocomplete
      :v-model="selectedName"
      :value="selectedName"
      @md-selected="getSelected"
      @md-changed="getOptions"
      :md-options="items"
      md-layout="box"
      >
      <label>Artículo</label>
      <div v-if="activeAddButton">
          <md-button class="md-primary md-raised btn-sm" v-if="brand != null" @click="activePrompt = true">Agregar</md-button>
      </div>
      <template slot="md-autocomplete-item" slot-scope="{ item }">{{ item.code }}</template>
      <template slot="md-autocomplete-empty">No se encontraron artículos</template>
    </md-autocomplete>

    <md-dialog-prompt
      :md-active.sync="activePrompt"
      v-model="new_article_code"
      md-title="¿Qué artículo querés agregar?"
      md-input-maxlength="255"
      md-input-placeholder="Código..."
      md-confirm-text="Enviar"
      @md-confirm="sendNewArticle" />

    <md-dialog-alert
      :md-active.sync="alert"
      :md-title="alert_title"
      :md-content="alert_content" />
  </div>
</template>

<script>
  export default {
    name: 'StockArticleSelector',
    data() {
      return {
        items: [],
        selected: null,
        selectedName: '',
        activePrompt: false,
        new_article_code: null,
        alert: false,
        alert_title: '',
        alert_content: ''
      }
    },
    props: {
      activeAddButton: {
        type: Boolean,
        default: true
      },
      brand: {
        type: Object,
        default: undefined
      }
    },
    watch: {
      brand(newValue) {
        axios.get("/api/stock/articles/"+newValue.id, {params: {query: ""}}).then(response => {
            this.items = response.data;
        });
        this.selected = null
        this.selectedName = ''
      }
    },
    methods: {
      getOptions(term){
         axios.get("/api/stock/articles", {params: {query: term}}).then(response => {
            this.items = response.data;
        }); 
      },
      getSelected(value) {
        this.selected = value
        this.selectedName = value.code
        this.$emit('updatearticle', value)
      },
      sendNewArticle(value) {
        if (value != null) {
          this.new_article_code = value
          axios.post('/api/stock/articles', {id_brand: this.brand.id, code: value })
          .then(response => {
            if (response.data.statusCode == 200){
              this.alert_title = "¡Bien papá!"
              this.selected = value
              this.selectedName = value
              this.items.push(response.data.data)
              this.$emit('updatearticle', response.data.data)
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
        this.getOptions("");
    }
  }
</script>

<style lang="scss" scoped>
  .search {
    max-width: 500px;
  }
</style>