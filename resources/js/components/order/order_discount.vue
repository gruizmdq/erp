<template>
    <div>
        <md-dialog :md-active.sync="showDialog">
            <md-dialog-title>Descuento</md-dialog-title>
            <div class="card-body">
                <!-- For now it s always percentage
                <div>
                    <md-radio v-model="discount.type" value="1">Porcentaje</md-radio>
                    <md-radio v-model="discount.type" value="2">Monto Fijo</md-radio>
                </div>-->
                <div class="form-group mt-3">
                    <label>Importe</label>
                    <span class="input-symbol z-depth-1" :class="symbol">
                        <input ref="input" type="number" min="0" v-model="discount.amount"/>
                    </span>
                </div>
                <div class="form-group mt-3">
                    <label>Comentarios</label>
                    <textarea class="form-control rounded-0" v-model="discount.comments" rows="3"></textarea>
                </div>
                <p v-if="textHelper" class="mt-3 red-text font-italic">
                    {{ textHelper }}
                </p>
            </div>
            <md-dialog-actions>
                <md-button class="md-primary" @click="showDialog = false">Cancelar</md-button>
                <md-button class="md-primary" @click="confirm">Confirmar</md-button>
            </md-dialog-actions>
        </md-dialog>

    <button type="button" v-if="showDescription == false && itemRow == false" class="btn-primary btn-sm" @click="showDialog = true">Crear descuento</button>
    <button type="button" v-if="showDeleteButton" class="btn-outline-danger btn-sm" @click="deleteDiscount">Borrar descuento</button>
   <!-- For now it s always for entire order
   <button v-if="showDescription == false && itemRow" class="btn-primary btn-sm" @click="showDialog = true"><i class="fas fa-plus"></i></button>
    
    <div v-if="showDescription">
        <div style="margin-bottom: .5rem;">
            Descuento en toda la orden
        </div>
        <span class="input-symbol z-depth-1" :class="symbol">
            <input type="number" min="0" v-model="discount.amount"/>
        </span>
        <button type="button" class="btn btn-outline-danger btn-sm waves-effect" @click="reset">Borrar</button>
    </div>-->

    </div>
</template>
<script>
export default {
    name: "OrderDiscount",
    data() {
        return {
            showDialog: false,
            discount: {
                type: '1',
                amount: null,
                comments: null,
            },            
            textHelper: null,
            showDescription: false,
            showDeleteButton: false
        }
    },
    props: {
        // para diferenciar entre toda la orden o en un simple artículo
        itemRow: {
            type: Boolean,
            default: false
        }
    },
    computed: {
        symbol() {
            return this.discount.type === '1' ? "percentage" : "money"
        }
    }, 
    methods: {
        confirm() {
            if (this.discount.type && this.discount.amount && this.discount.comments) {
                this.showDialog = false
                this.showDescription = true
                this.textHelper = null
                this.$emit('update', this.discount)
                this.showDeleteButton = true
            }
            else {
                this.textHelper = "Completar todos los campos"
            }
        },
        deleteDiscount() {
            this.reset()
            this.showDeleteButton = false
        },
        reset() {
            this.showDialog = false
            this.discount.type = '1'
            this.discount.amount = null
            this.discount.comments = null
            this.textHelper = null
            this.showDescription = false
            this.$emit('update', null)
        }
    }
}
</script>
<style scoped>
    .input-symbol {
        position: relative;
    }
    .input-symbol input {
        padding-left: 20px !important;
        height: calc(1.5em + .75rem + 2px);
        padding: .375rem .75rem;
    }
    .input-symbol:before {
        position: absolute;
        top: 0;
        left: 5px;
    }
    .percentage:before{
        content:"%";
    }
    .money:before{
        content:"$";
    }
</style>