<template>
  <div>
      <div class="md-layout-item">
        <md-field>
            <md-select v-model="sucursal" name="Sucursal">
                <md-option v-for="item in options" :value="item.id" :key="item.name">{{ item.name }}</md-option>
            </md-select>
        </md-field>
      </div>

  </div>
</template>

<script>
  export default {
    name: 'SucursalSelector',
    data() { 
      return{
        sucursal: null,
      }
    },
    props: {
      options: {
        type: Array,
        default: true
      },
    }
  }
</script>

<style lang="scss" scoped>
  
</style>