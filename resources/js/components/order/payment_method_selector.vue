<template>
    <div>
        <label>Método de pago</label>
        <select ref="select" @keydown.tab="changeSelected" v-model="selected" v-for="i in options" :key="i.id" class="custom-select" @change="$emit('update', selected)">
            <option :value="i">{{ i.name }}</option>
        </select>
    </div>
</template>
<script>
export default {
    name: 'PaymentMethodSelector',
    data() {
        return {
            selected: null,
            options: []
        }
    },
    methods: {
        getData() {
            axios.get('/api/order/get_payment_methods')
            .then(response => {
                this.options = response.data;
            });
        },
        changeSelected(e) {
            if (this.selected == null)
                e.preventDefault()
        }
    },
    created() {
        this.getData()
    }

}
</script>