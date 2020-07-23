<template>
    <tr>
        <td scope="col">{{ item.barcode }}</td>
        <td scope="col">{{ item.brand_name }} - {{ item.code }} - {{ item.color }} - Nro: {{ item.number }}</td>
        <td scope="col">${{ item.price }}</td>
        <td scope="col">${{ item.subtotal.toLocaleString() }}</td>
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
    }
}
</script>