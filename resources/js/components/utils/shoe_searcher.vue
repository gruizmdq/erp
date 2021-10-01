<template>
    <div>
        <md-dialog :md-active.sync="showDialog">
            <form novalidate class="md-layout" @submit.prevent="validateForm" autocomplete="off">
                <md-card class="md-layout-item">
                    <md-card-header>
                        <div class="md-title">Buscar artículo</div>
                    </md-card-header>

                    <md-card-content>
                        <div class="md-layout md-gutter">
                            <div class="md-layout-item">
                                <md-field>
                                    <label for="brand">Marca</label>
                                    <md-input @keydown.right="$refs.article.$el.focus()" @keydown.down="$refs.article.$el.focus()" ref="brand" name="brand" id="brand" v-model="brand" :disabled="sending" />
                                </md-field>
                            </div>

                            <div class="md-layout-item">
                                <md-field>
                                    <label for="article">Artículo</label>
                                    <md-input @keydown.down="$refs.color.$el.focus()" @keydown.right="$refs.color.$el.focus()" @keydown.left="$refs.brand.$el.focus()" @keydown.up="$refs.brand.$el.focus()" ref="article" name="article" id="article" v-model="article" :disabled="sending" />
                                </md-field>
                            </div>

                            <div class="md-layout-item">
                                <md-field>
                                    <label for="color">Color</label>
                                    <md-input @keydown.down="$refs.search.$el.focus()" @keydown.up="$refs.article.$el.focus()" @keydown.left="$refs.article.$el.focus()" ref="color" name="color" id="color" v-model="color" :disabled="sending" />
                                </md-field>
                            </div>
                        </div>
                    </md-card-content>

                    <md-progress-bar md-mode="indeterminate" v-if="sending" />

                    <md-card-actions>
                        <md-button @keydown.up="$refs.color.$el.focus()" ref="search" type="submit" class="md-primary focus" :disabled="sending">Buscar</md-button>
                    </md-card-actions>
                </md-card>
            </form>

            <div v-if="articleList.length > 0">
                <hr class="py-2">
                <ul>
                    <li v-for="(article) in articleList" :key="article.id">
                        <button @click="sendItem" type="submit" @submit="sendItem" @keydown.down="moveDown" @keydown.up="moveUp" ref="items" class="item">{{ getDescription(article) }}</button>
                    </li>
                </ul>
            </div>

        </md-dialog>

        <button type="button" @keydown.up="$emit('keydownUp')" ref="openModalButton" class="focus" @click="showModal()">Buscar</button>
    </div>
</template>
<script>
export default {
    name: "ShoeSearcherComponent",
    data() { 
      return{
        brand: null,
        article: null,
        color: null,
        sending: false,
        showDialog: false,
        articleList: [],
        articleSelected: null,
        indexSelected: -1,
      }
    },
    methods: {
        showModal() {
            this.showDialog = true
            this.$nextTick(() => {
                let input = this.$refs.brand.$el
                input.focus()
            });
        },
        getDescription(article) {
            return `${article.brand_name} - ${article.code} - ${article.color_name} - Nro: ${article.number}`.toUpperCase()
        },
        validateForm() {
            this.findArticles();
        },
        findArticles() {
            this.sending = true;
            axios.get('/api/stock/articles/find', {params: { article_name: this.article, brand_name: this.brand, color_name: this.color }})
            .then(response => {
                this.articleList = response.data.data
                if (response.data.data.length > 0) {
                    this.articleSelected = this.articleList[0]
                    this.indexSelected = 0
                    this.$nextTick(() => {
                        let input = this.$refs.items[this.indexSelected];
                        input.focus();
                    });
                }
                this.sending = false
            });
        },
        moveDown(e) {
            if (this.indexSelected < this.articleList.length - 1) {
                e.preventDefault()
                this.indexSelected++
            }
            this.articleSelected = this.articleList[this.indexSelected]
            this.$nextTick(() => {
                let input = this.$refs.items[this.indexSelected];
                input.focus();
            });
        },
        moveUp(e) {
            if (this.indexSelected > 0 ) {
                e.preventDefault()
                this.indexSelected--
                this.articleSelected = this.articleList[this.indexSelected]
                this.$nextTick(() => {
                    let input = this.$refs.items[this.indexSelected];
                    input.focus();
                });
            }
            else {
                this.$refs.color.$el.focus()
            }            
        },
        sendItem() {
            this.$emit('updateArticle', this.articleSelected)
            this.reset()
        },
        reset() {
            this.showDialog = false
            this.brand = null
            this.article = null
            this.color = null
            this.sending = false
            this.articleList = []
            this.articleSelected = null
            this.indexSelected = -1
        }
    },
    mounted() {
        document.addEventListener('keydown', (e) => {
            if (e.code === "Escape") {
                this.articleSelected = null
                this.sendItem()
            }
        });
    }

}
</script>
<style scoped>
  .md-progress-bar {
    position: absolute;
    top: 0;
    right: 0;
    left: 0;
  }
  .item {
    border: none;
    background: white;
    padding: 5px
  }
  .item:focus {
    background: #448aff;
    color: white!important;
  }
  .focus:focus{
    background: black;
    color: white;
  }
  ul {
    list-style: none;
  }
</style>