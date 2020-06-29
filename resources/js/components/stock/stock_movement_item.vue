<template>
  <tr>
    <td scope="row">{{ item.number }}</td>
    <td v-for="s in sucursals" :key="s" scope="col">{{ getStock(s) }}</td>
    <td scope="col"><sucursal-selector :options="sucursals"></sucursal-selector></td>
    <td scope="col"><sucursal-selector :options="sucursals"></sucursal-selector></td>
    <td scope="col"><input v-model.number="qty" type="number" min="0" step="1" class="m-auto text-center form-control form-control-sm" style="max-width: 100px;"></td>
  </tr>
</template>

<script>
  export default {
    name: 'StockMovementItem',
    data() {
      return {
        qty: null
      }
    },
    props: {
        item: {
            type: Object,
            default: null
        },
        sucursals: {
            type: Array,
            default: null
        }
    },
    methods: {
      getStock(sucursal) {
        for( var i = 0; i < this.item.shoe_sucursal_item.length; i ++)
          if (this.item.shoe_sucursal_item[i].id_sucursal == sucursal.id)
            return this.item.shoe_sucursal_item[i].stock
        return 0
      }
    }
  }
</script>

<style lang="scss" scoped>
  
</style>