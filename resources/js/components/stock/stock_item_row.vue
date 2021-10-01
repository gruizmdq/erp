<template>
  <tr :class="background" >
    <th scope="row">{{ item.number }}</th>
    <td>{{ stock }}</td>
    <td><input @keydown.up="moveUp($event, 'qty')" @keydown.enter="moveDown($event, 'qty')" @keydown.down="moveDown($event, 'qty')" @keydown.right="$refs.buy_price.focus()" ref="qty" required type="number" :id="'stock-aded-'+item.number" style="max-width: 100px" class="m-auto text-center form-control form-control-sm" placeholder=""  v-model.number="item.stock_to_add" min="0" step="1"></td>
    <td><input @keydown.up="moveUp($event, 'buy_price')" @keydown.enter="moveDown($event, 'buy_price')" @keydown.down="moveDown($event, 'buy_price')" @keydown.left="$refs.qty.focus()" @keydown.right="$refs.sell_price.focus()" ref="buy_price" required step="0.01" type="number" :id="'buy-price-'+item.number" style="max-width: 100px" class="m-auto text-center form-control form-control-sm" v-model.number="item.buy_price" min="0"></td>
    <td><input @keydown.up="moveUp($event, 'sell_price')" @keydown.enter="moveDown($event, 'sell_price')" @keydown.down="moveDown($event, 'sell_price')" @keydown.left="$refs.buy_price.focus()" @keydown.right="$refs.tiendanube.focus()" ref="sell_price" required step="0.01" type="number" :id="'sell-price-'+item.number" style="max-width: 100px" class="m-auto text-center form-control form-control-sm" v-model.number="item.sell_price" min="0"></td>
    <td>{{ getProfit }}</td>
    <td>
        <div class="custom-control custom-checkbox">
            <input @keydown.up="moveUp($event, 'tiendanube')" @keydown.enter="moveDown($event, 'tiendanube')" @keydown.down="moveDown($event, 'tiendanube')" @keydown.left="$refs.sell_price.focus()" @keydown.right="$refs.marketplace.focus()" ref="tiendanube" v-model="item.available_tiendanube" type="checkbox" class="custom-control-input" :id="'tiendaNube-'+item.number">
            <label class="custom-control-label" :for="'tiendaNube-'+item.number"></label>
        </div>
    </td>
    <td>
        <div class="custom-control custom-checkbox">
            <input @keydown.up="moveUp($event, 'marketplace')" @keydown.enter="moveDown($event, 'marketplace')" @keydown.down="moveDown($event, 'marketplace')" @keydown.left="$refs.tiendanube.focus()" ref="marketplace" v-model="item.available_marketplace" type="checkbox" class="custom-control-input" :id="'marketplace-'+item.number">
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
        },
        index: {
            type: Number,
            default: 0
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
        },
        getProfit() {
          if (this.item.sell_price && this.item.buy_price)
            return ((this.item.sell_price / this.item.buy_price * 100) - 100).toLocaleString(2) + " %"
          return ""
        }
    },
    methods: {
      moveDown(e, ref) {
        e.preventDefault()
        this.$emit('moveDown', this.index, ref)
      },
      moveUp(e, ref) {
        e.preventDefault()
        this.$emit('moveUp', this.index, ref)
      }
    }
  }
</script>

<style lang="scss" scoped>
  .green {
    background: #c8e6c9!important;
  }
</style>