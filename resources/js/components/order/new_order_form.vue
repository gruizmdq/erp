<template>
	<div>
		<form class="form white z-depth-1 p-3">
			<div class="row">
				<div class="col-md-4">
					<div class="form-group">
						<label for="client">Cliente</label>
						<input type="text" id="client" class="form-control" placeholder="Cliente" />
					</div>
					<user-selector label="Vendedor" @update="updateSeller" role="seller"></user-selector>
					<user-selector label="Cajero" @update="updateCashier" role="cashier"></user-selector>
				</div>
			</div>

			<hr />

			<div class="form-row mb-4 mt-4">
				<div class="col-3">
					<label for="article">Artículo</label>
            		<input @keydown.tab="disableTab" ref="article" id="article" type="text" class="form-control"  v-model="barcode" @change="getDetailItem">
				</div>

				<div class="col-md-9">
					<new-item-description @confirmitem="confirmItem" v-if="newItemDescription" :item="newItemDescription"></new-item-description>
					<h4 v-if="notFoundItemMessage">No se encontró el artículo</h4>
				</div>
			</div>

			<hr />

			<table class="table">
				<thead>
					<tr>
						<th scope="col">Código</th>
						<th scope="col">Descripción</th>
						<th scope="col">Precio</th>
						<th scope="col">Subtotal</th>
						<th scope="col">Descuento</th>
						<th scope="col">Cantidad</th>
					</tr>
				</thead>
				<tbody>
					<new-order-row v-for="i in items" :key="i.code" :item.sync="i"></new-order-row>
				</tbody>
			</table>
			<hr />
			<order-discount @update="updateOrderDiscount"></order-discount>
			<hr />
			<div v-if="items.length > 0" class="row">
				<div class="col-md-6 mt-3">
					<div>
						<h4>Pago</h4>
						<div class="form-group mt-3">
							<payment-method-selector ref="paymentMethodSelector" @update="updatePaymentMethod"></payment-method-selector>
						</div>
						<div v-if="paymentMethod && paymentMethod.name === 'Efectivo'" class="form-group mt-3">
							<label>Monto</label>
							<input type="number" :min="total" v-model="paidMoney"/>
							<h4 v-if="paidMoney" class="mt-3">Cambio: ${{ change }} pelotudo!</h4>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="mt-3">
						<h3>Subtotal: <strong>${{ subtotal.toLocaleString() }}</strong></h3>
					</div>
					<hr />
					<h3 v-if="orderDiscount && orderDiscount.amount > 0">Descuento: <strong> % {{ orderDiscount.amount }} </strong></h3>
					<h3 v-if="paymentMethod && paymentMethod.charge > 0">Recargo: <strong>% {{paymentMethod.charge}}</strong></h3>
					<hr>
					<h2>Total: <strong>${{ total.toLocaleString() }}</strong></h2>
				</div>
			</div>

			<div class="row">
				<div class="col-md-12">
					<div class="form-group">
						<label for="comments">Comentarios</label>
						<textarea class="form-control rounded-0" id="comments" rows="1"></textarea>
					</div>
				</div>
			</div>

			<div class="buttons text-right">
				<button type="button" class="btn btn-primary waves-effect">Confirmar</button>
				<button type="button" class="btn btn-outline-primary waves-effect">Borrar</button>
			</div>
		</form>
	</div>

</template>
<script>
	export default {
		 name: 'NewOrderForm',
		 data() {
			 return {
				paidMoney: null,
				barcode: null,
				seller: null,
				cashier: null,
				items: [],
				orderDiscount: null,
				newItemDescription: null,
				subtotal: 0,
				discount: 0,
				paymentMethod: null,
				notFoundItemMessage: false
			 }
		 },
		 computed: {
			 total () {
				let subtotal = this.subtotal
				if (this.orderDiscount)
					subtotal = subtotal * (1 - this.orderDiscount.amount / 100)
				if (this.paymentMethod)
					subtotal = subtotal * (1 - this.paymentMethod.charge / 100)
				 return Number(subtotal.toFixed(2))
			 },
			 change() {
				 return (this.paidMoney - this.total).toLocaleString()
			 }
		 },
		 methods: {
			confirmItem(price, discount, subtotal) {
				this.newItemDescription.discount = discount
				this.newItemDescription.subtotal = subtotal
				this.newItemDescription.price = price
				this.newItemDescription.qty = 1
				this.items.unshift(this.newItemDescription)
				this.subtotal += this.newItemDescription.subtotal
				this.newItemDescription = null
				this.$refs.article.focus()
			},
			disableTab(e) {
				if (this.barcode != null || (this.items.length === 0 && this.newItemDescription == null) )
					e.preventDefault()
			}, 
			updatePaymentMethod(value) {
				this.paymentMethod = value
			},
			updateOrderDiscount(value) {
				console.log(value)
				this.orderDiscount = value
				this.$refs.paymentMethodSelector.$refs.select[0].focus()
			},
			updateSeller(value) {
				this.seller = value
			},
			updateCashier(value) {
				this.cashier = value
			},
			getDetailItem(e) {
				if (this.barcode != null) {
					axios.get('/api/stock/get_detail_item_barcode', {params: { barcode: this.barcode }})
					.then(response => {
						let data = response.data
						this.barcode = null
						if (data.item.length !== 0) {
							this.newItemDescription = response.data.item
							this.newItemDescription.qty = 1
							return
						}
						else
							this.newItemDescription = null
					})
					.finally(() => this.$refs.article.focus())
				}
			}
		}
	}
</script>