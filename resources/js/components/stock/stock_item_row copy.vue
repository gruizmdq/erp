<template>
  <tr :class="background" >
    <th scope="row">{{ data.number }}</th>
    <td>{{ data.stock }}</td>
    <td><input required type="number" :id="'stock-aded-'+data.number" style="max-width: 100px" class="m-auto text-center form-control form-control-sm" placeholder="" :value="data.stock" :model="stock_to_add"></td>
    <td><input required step="0.01" type="number" :id="'buy-price-'+data.number" style="max-width: 100px" class="m-auto text-center form-control form-control-sm" :value="data.buy_price" :model="data.buy_price"></td>
    <td><input required step="0.01" type="number" :id="'sell-price-'+data.number" style="max-width: 100px" class="m-auto text-center form-control form-control-sm" :value="data.sell_price" :model="data.sell_price"></td>
    <td>
        <div class="custom-control custom-checkbox">
            <input :checked="data.available_tiendanube" type="checkbox" class="custom-control-input" :id="'tiendaNube-'+data.number">
            <label class="custom-control-label" :for="'tiendaNube-'+data.number"></label>
        </div>
    </td>
    <td>
        <div class="custom-control custom-checkbox">
            <input :checked="data.available_marketplace" type="checkbox" class="custom-control-input" :id="'marketplace-'+data.number">
            <label class="custom-control-label" :for="'marketplace-'+data.number"></label>
        </div>
    </td>
  </tr>
</template>

<script>
  export default {
    name: 'StockItemRow',
    data() {
      return {
        data: this.item,
        stock: this.item.stock
      }
    },
    props: {
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
          this.data.stock = newValue
        },
        new_buy_price(newValue) {
          this.data.buy_price = newValue
        },
        new_sell_price(newValue) {
          this.data.sell_price = newValue
        },
        tiendanube(newValue) {
          this.data.available_tiendanube = newValue
        },
        marketplace(newValue) {
          this.data.available_marketplace = newValue
        }
    },
    computed: {
        background() {
          return this.data.barcode != undefined ? 'green' : ''
        }
    },
    methods: {
        
    }
  }
</script>

<style lang="scss" scoped>
  
</style>