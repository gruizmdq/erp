<template>
  <tr :class="background" >
    <th scope="row">{{ item.number }}</th>
    <td>{{ stock }}</td>
    <td><input required type="number" :id="'stock-aded-'+item.number" style="max-width: 100px" class="m-auto text-center form-control form-control-sm" placeholder=""  v-model.number="item.stock_to_add"></td>
    <td><input required step="0.01" type="number" :id="'buy-price-'+item.number" style="max-width: 100px" class="m-auto text-center form-control form-control-sm" v-model.number="item.buy_price" ></td>
    <td><input required step="0.01" type="number" :id="'sell-price-'+item.number" style="max-width: 100px" class="m-auto text-center form-control form-control-sm" v-model.number="item.sell_price"></td>
    <td>
        <div class="custom-control custom-checkbox">
            <input v-model="item.available_tiendanube" type="checkbox" class="custom-control-input" :id="'tiendaNube-'+item.number">
            <label class="custom-control-label" :for="'tiendaNube-'+item.number"></label>
        </div>
    </td>
    <td>
        <div class="custom-control custom-checkbox">
            <input v-model="item.available_marketplace" type="checkbox" class="custom-control-input" :id="'marketplace-'+item.number">
            <label class="custom-control-label" :for="'marketplace-'+item.number"></label>
        </div>
    </td>
  </tr>
</template>

<script>
  export default {
    name: 'StockItemRow',
    data() {
      return {
        //stock: this.item.stock
      }
    },
    props: {
        stock: {
            type: Number,
            default: 0
        },
        item: {
            type: Object,
            default: null
        },
        new_stock: {
            type: Number,
            default: 0
        },
        new_buy_price: {
            type: Number,
            default: 0
        },
        new_sell_price: {
            type: Number,
            default: 0
        },
        marketplace: {
            type: Boolean,
            default: null
        },
        tiendanube: {
            type: Boolean,
            default: null
        }
    },
    watch: {
        new_stock(newValue) {
          this.item.stock_to_add = newValue
        },
        new_buy_price(newValue) {
          this.item.buy_price = newValue
        },
        new_sell_price(newValue) {
          this.item.sell_price = newValue
        },
        tiendanube(newValue) {
          this.item.available_tiendanube = newValue
        },
        marketplace(newValue) {
          this.item.available_marketplace = newValue
        }
    },
    computed: {
        background() {
          return this.item.barcode != undefined ? 'green' : ''
        }
    }
  }
</script>

<style lang="scss" scoped>
  
</style>