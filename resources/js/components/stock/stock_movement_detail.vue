<template>
    <div>
        <div class="row" v-if="movement">
            <div class="col-md-12">
                <div class="card">
                    <div class='card-body'>
                        <h4>Fecha: <strong>{{ getDate() }}</strong></h4>
                        <h4>Desde <strong>{{ movement.sucursals.sucursal_from }}</strong> hacia <strong>{{ movement.sucursals.sucursal_to }}</strong></h4>
                        <h3>Zapatillas:</h3>
                        <table class="table">
                            <thead>
                                <tr>
                                    <td>Marca</td>
                                    <td>Artículo</td>
                                    <td>Color</td>
                                    <td>Número</td>
                                    <td>Cantidad</td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, index) in movement.items" :key="index">
                                    <td>{{ item.brand }}</td>
                                    <td>{{ item.code }}</td>
                                    <td>{{ item.color }}</td>
                                    <td>{{ item.number }}</td>
                                    <td>{{ item.qty }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <h3>Comentarios: {{ item.description }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
const id = location.href.substring(location.href.lastIndexOf('/') + 1)

export default {
    name: "MovementDetailComponent",
    data() {
        return {
            movement: null
        }
    },
    methods: {
        getMovement() {
            axios.get('/api/stock/movements/'+id)
            .then(response => {
                this.movement = response.data
            })
        },
        getDate() {
            let date = new Date(this.movement.created_at)
            return date.getDate() + "/" + (date.getMonth()+1) + "/" + date.getFullYear()
        }
    },
    mounted() {
        this.getMovement()
    }
}
</script>