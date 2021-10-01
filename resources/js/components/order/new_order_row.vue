<template>
    <tr>
        <td scope="col">{{ item.barcode }}</td>
        <td scope="col" class="bold">{{ getDescription() }}</td>
        <td scope="col">${{ item.price }}</td>
        <td scope="col" class="bold">${{ item.subtotal.toLocaleString() }}</td>
        <td scope="col">{{ item.discount }}%</td>
        <td scope="col">{{ item.qty }}</td>
    </tr>
</template>
<script>
export default {
    name: "NewOrderRow",
    props: {
        item: {
            type: Object,
            default: null
        }
    },
    methods: {
        getDescription() {
            return `${this.item.brand_name} - ${this.item.code} - ${this.item.color} - Nro: ${this.item.number}`.toUpperCase()
        }
    }
}
</script>
<style scoped>
    .bold{
        font-weight: bold;
    }
    td {
        font-size: 16px;
    }
</style>