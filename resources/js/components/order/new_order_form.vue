<template>
	<div>
		<form class="form white z-depth-1 p-3">
			<div class="row">
				<div class="col-md-4">
					<div class="form-group">
						<label ref="client" for="client">Cliente</label>
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
					<new-order-row v-for="i in items" :key="'item-'+i.id+Math.floor(Math.random() * 255)" :item="i"></new-order-row>
				</tbody>
			</table>
			<hr />
			<order-discount v-if="items && items.length > 0" @update="updateOrderDiscount"></order-discount>
			<hr />
			<div v-if="items.length > 0" class="row">
				<div class="col-md-6 mt-3">
					<div>
						<h4><strong>Pago</strong></h4>
						<div v-if="paymentMethods.length > 0" class="mt-3">
							<h6 v-for="payment in paymentMethods" :key="'payment-'+payment">${{ payment.amount }} - {{ paymentDescription(payment) }}</h6>
							<hr />	
						</div>
						<div class="form-group mt-3">
							<payment-method-selector :amountToPay.sync="amountToPay" ref="paymentMethodSelector" @update="updatePaymentMethod"></payment-method-selector>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="mt-3">
						<h3>Subtotal: <strong>${{ subtotal.toLocaleString() }}</strong></h3>
					</div>
					<hr />
					<h3 v-if="orderDiscount && orderDiscount.amount > 0">Descuento: <strong> % {{ orderDiscount.amount }} </strong></h3>
					<div>
						<h3>Recargo: <strong> ${{ getCharge }}</strong></h3>
					</div>
					<hr>
					<h2>Total: <strong>${{ total.toLocaleString() }}</strong></h2>
				</div>
			</div>

			<div class="row mt-3">
				<div class="col-md-12">
					<div class="form-group">
						<label for="comments">Comentarios</label>
						<textarea @keydown.esc="$refs.paymentMethodSelector.$refs.method.focus()" ref="comments" v-model="comment" class="form-control rounded-0" id="comments" rows="1"></textarea>
					</div>
				</div>
			</div>

			<div class="buttons text-right">
				<button v-if="paymentMethod && (amountToPay - paymentMethod.amount <= 0)" ref="sendOrder" type="button" class="boton" @keydown.esc="$refs.comments.focus()" @click="sendOrder">Confirmar</button>
				<button type="button" class="boton" @keydown.esc="$refs.sendOrder.focus()" @keydown.tab="$event.preventDefault()" @click="activeDeleteDialog = true">Borrar</button>
			</div>
		</form>

		<md-dialog-confirm
			:md-active.sync="activeDeleteDialog"
			md-title="¿Seguro querés borrar cancelar esta orden?"
			md-confirm-text="Dale mecha"
			md-cancel-text="Cancelar"
			@md-confirm="sendResetNotification" />

		<md-dialog-alert
			:md-active.sync="alertActive"
			:md-title="alertTitle"
			:md-content="alertContent" />
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
				paymentMethods: [],
				comment: null,

				notFoundItemMessage: false,
				activeDeleteDialog: false,

				alertActive: false,
				alertTitle: null,
				alertContent: null
			 }
		 },
		 computed: {
			 amountToPay() {
				var amountToPay = this.subtotal
				if (this.orderDiscount)
					amountToPay = amountToPay * (1 - this.orderDiscount.amount / 100)
				this.paymentMethods.forEach(element => {
					if (element.option)
						amountToPay = amountToPay - ( element.amount / (1 + element.option.charge / 100))
					else
						amountToPay = amountToPay - element.amount
				})
				return amountToPay
			 },
			 total() {
				let subtotal = Number(this.subtotal)
				if (this.orderDiscount)
					subtotal = subtotal * (1 - this.orderDiscount.amount / 100)

				subtotal += this.getCharge
				return subtotal
			 },
			 change() {
				 return (this.paidMoney - this.total).toLocaleString()
			 },
			 getCharge() {
				 var charge = 0
				 this.paymentMethods.forEach(element => {
					if (element.option)
						charge = charge + ( element.amount * element.option.charge / 100)
				 })
				 if (this.paymentMethod && this.paymentMethod.option)
				 	charge = charge + ( this.paymentMethod.amount * this.paymentMethod.option.charge / 100)
				 return charge
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
			updatePaymentMethod(payment, needOther, nextFocus = true) {
				if (needOther)
					this.paymentMethods.push(payment)
				else {
					this.paymentMethod = payment
				}
				if (nextFocus)
					this.$refs.comments.focus()
			},
			updateOrderDiscount(value) {
				this.orderDiscount = value
				this.$refs.paymentMethodSelector.$refs.method[0].focus()
			},
			updateSeller(value) {
				this.seller = value
			},
			updateCashier(value) {
				this.cashier = value
			},
			getDetailItem(e) {
				if (this.barcode != null) {
					axios.get('/api/stock/detail_item_barcode', {params: { barcode: this.barcode }})
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
			},
			paymentDescription(payment) {
				if (payment.method.name != "Tarjeta")
					return payment.method.name

				if (!payment.option)
					return `${payment.method.name} ${payment.card.name}`

				let plural = ''

				if (payment.option.installments > 1) {
					plural = 's'
				}
				let installment = (payment.amount / payment.option.installments).toLocaleString()
				return `${payment.option.installments} cuota${plural} de $${installment} (${payment.card.name})`
			},
			sendOrder() {
				axios.post('/api/order', { order: this.createRequest() })
				.then(response => {
					this.alertTitle = response.data.status
					this.alertContent = response.data.message
					this.alertActive = true
					this.resetOrder()
				})
			},
			sendResetNotification() {
				axios.post('/api/order/reset', { id_cashier: this.cashier.id, id_seller: this.seller.id })
				.then(response => {
					this.resetOrder()
				})
			},
			resetOrder() {
				this.paidMoney = null
				this.barcode = null
				this.seller = null
				this.cashier = null
				this.items = []
				this.orderDiscount = null
				this.newItemDescription = null
				this.subtotal = 0
				this.discount = 0
				this.comment = null
				this.paymentMethods = []
				this.notFoundItemMessage = false
				this.activeDeleteDialog = false

				this.$refs.client.focus()	
			},
			createRequest() {
				let data = {}
				data.seller = this.seller.id
				data.cashier = this.cashier.id
				//TODO cambiar esto en futuro para agrupar items iguales
				data.qty = this.items.length
				data.items = this.items.map(item => ({
					id: item.id,
					qty: item.qty,
					buy_price: item.buy_price,
					sell_price: item.sell_price,
					price: item.price
				}))
				data.orderDiscount = this.orderDiscount
				data.subtotal = parseFloat(this.subtotal)
				data.total = parseFloat(this.total)
				data.discount = this.discount
				//add payment methods
				data.paymentMethods = this.paymentMethods
				//Append lastone
				data.paymentMethods.push(this.paymentMethod)
				data.comment = this.comment
				
				return data
			}
		},
		mounted() {
			this.$refs.client.focus()	
		}
	}
</script>
<style scoped>
	.boton:focus {
		background: black;
		color: white;
	}
</style>