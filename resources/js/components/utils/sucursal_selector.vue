<template>
  <div>
      <div v-if="options.length > 0" class="md-layout-item">
        <md-field class="white z-depth-1" >
            <label :for="label">{{ label }}</label>
            <md-select :required="required" v-model="sucursal" :id="label" name="Sucursal" @md-selected="updateSucursal">
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
        default: []
      },
      required: {
          type: Boolean,
          default: true
      },
      label: {
          type: String,
          default: ''
      }
    },
    methods: {
      updateSucursal(value) {
        this.$emit('updatesucursal', value)
      }
    }
  }
</script>

<style lang="scss" scoped>
  
</style>