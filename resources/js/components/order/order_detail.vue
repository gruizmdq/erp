<template>
    <div>
        <div class="row" v-if="order">
            <div class="col-md-12">
                <div class="card p-3">
                    <p>Canal de Venta: <strong>{{ order.description.sucursal }}</strong></p>
                    <p>Vendedor: <strong>{{ order.description.seller }}</strong></p>
                    <p class="m-0">Cajero: <strong>{{ order.description.cashier }}</strong></p>
                </div>
            </div>
            <div class="mt-3 col-md-12">
                <div class="card p-3">
                    <h4>Items</h4>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th scope="col">Marca</th>
                                <th scope="col">Artículo</th>
                                <th scope="col">Color</th>
                                <th scope="col">Número</th>
                                <th scope="col">Precio Compra</th>
                                <th scope="col">Precio Venta</th>
                                <th scope="col">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="i in order.items" :key="i">
                                <th>{{ i.brand }}</th>
                                <td>{{ i.code }}</td>
                                <td>{{ i.color }}</td>
                                <td>{{ i.number }}</td>
                                <td>${{ i.buy_price }}</td>
                                <td>${{ i.sell_price }}</td>
                                <td>${{ i.total }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="row mt-3" v-if="order">
            <div class="col-md-6" v-if='payment_type.length > 0'>
                <div class="card p-3">
                    <h4>Pago</h4>
                    <p v-for="payment in order.payments" :key="payment.id">{{ payment_type[payment.id_payment_method-1].name }}: <strong>${{ payment.total}}</strong></p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card p-3">
                    <p>Precio de Compra: <strong>$123</strong></p>
                    <p>Precio de Venta: <strong>$123</strong></p>
                    <hr>
                    <p>Subtotal: <strong>${{ order.subtotal }}</strong></p>
                    <p>Descuento: <strong>$0</strong></p>
                    <p>Recargo: <strong>$0</strong></p>
                    <hr>
                    <p>Total: <strong>${{ order.total }}</strong></p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <button @click="deleteOrder" class="btn btn-sm btn-primary">Borrar</button>
            </div>
        </div>
    </div>
</template>
<script>

const id = location.href.substring(location.href.lastIndexOf('/') + 1)

export default {
    name: "OrderDetail",
    data() {
        return {
            id: null,
            order: null, 

            payment_type: []
            //TODO DO mejorar esto
        }
    },
    methods: {
        getOrder() {
            axios.get('/api/order/detail/'+id)
            .then(response => {
                console.log(response.data)
                this.order = response.data
            })
        },
        getPaymentsType() {
            axios.get('/api/order/payment_methods/')
            .then(response => {
                console.log(response.data)
                this.payment_type = response.data
            })    
        },
        deleteOrder() {
            alert('lito')
        }
    },
    mounted() {
        this.getOrder()
        this.getPaymentsType()
    }
}
</script>