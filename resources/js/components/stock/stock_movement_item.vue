<template>
  <tr>
    <td scope="row">{{ item.brand_name }}</td>
    <td scope="row">{{ item.code }}</td>
    <td scope="row">{{ item.color }}</td>
    <td scope="row">{{ item.number }}</td>
    <td v-for="s in sucursals" :key="s.id + Math.random()" scope="col">{{ getStock(s) }}</td>
    <td scope="col">{{ item.qty }}</td>
  </tr>
</template>

<script>
  export default {
    name: 'StockMovementItem',
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