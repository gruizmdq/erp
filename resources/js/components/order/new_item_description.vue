<template>
    <div>
        <table class="table">
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th>Número</th>
                    <th>Precio Venta</th>
                    <th>Precio</th>
                    <th>Subtotal</th>
                    <th>Descuento</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ item.brand_name }} - {{ item.code }} - {{ item.color }}</td>
                    <td>{{ item.number }}</td>
                    <td>{{ item.sell_price }}</td>
                    <td>
                        <span class="input-symbol z-depth-1 money">
                            <input v-model="price" type="number" min="0">
                        </span>
                    </td>
                    <td>{{ subtotal }}</td>
                    <td>
                        <span class="input-symbol z-depth-1 percentage">
                            <input min="0" max="100" type="number" v-model="discount">
                        </span>
                    </td>
                    <td><input class="boton" type="button" @click="$emit('confirmitem', price, discount, subtotal)" value="Aceptar"></td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
<script>
export default {
    name: "NewItemDescription",
    data() {
        return {
            discount: 0,
            price: this.item.sell_price
        }
    },
    props: {
        item: {
            type: Object,
            default: null
        }
    },
    computed: {
        subtotal() {
            return this.discount < 100 ? Number((this.price * (1 - this.discount / 100)).toFixed(2)) : 0
        }
    }
}
</script>
<style lang="scss" scoped>
    button:focus{
        background: black;
        color: white;
    }
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
    .percentage:before {
        content:"%";
    }
    .money:before {
        content:"$";
    }
    .boton:focus {
        background: black;
        color: white;
    }
</style>