<template>
	<div>
		<form class="form white z-depth-1 p-3" autocomplete="off">
			<div class="row">
				<div class="col-md-4">
				<!--	<div class="form-group">
						<label @keydown.enter="$refs.seller.$refs.user.focus()" ref="client" for="client">Cliente</label>
						<input type="text" id="client" class="form-control" placeholder="Cliente" />
					</div> -->
					<user-selector ref="seller" label="Vendedor" @update="updateSeller" role="seller" :actualUser.sync="seller"></user-selector>
					<user-selector ref="cashier" label="Cajero" @update="updateCashier" role="cashier" :actualUser.sync="cashier"></user-selector>
				</div>
			</div>

			<hr />
			<div class="form-row mb-4 mt-4">
				<div class="col-3">
					<div>
						<label for="article">Artículo</label>
            			<input @keydown.down="$refs.articleSearcher.$refs.openModalButton.focus()" @keydown.right="moveTo('itemDescription', 'price')" @keydown.tab="disableTab" ref="article" id="article" type="text" class="form-control"  v-model="barcode" @change="getDetailItem(barcode, false)">
					</div>
					<div class="mt-1">
						<shoe-searcher @keydownUp="$refs.article.focus()" ref="articleSearcher" @updateArticle="updateArticle"></shoe-searcher>
					</div>
				</div>

				<div class="col-md-9">
					<new-item-description ref="itemDescription" @confirmitem="confirmItem" v-if="newItemDescription" :item="newItemDescription"></new-item-description>
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
			<div class="row">
				<div class="col-3">
					<order-discount v-if="items && items.length > 0" @update="updateOrderDiscount"></order-discount>
				</div>
				<div class="col-3">
		    		<button class="boton" type="button" @click="activateShowChangeFlow">Devolver artículo</button>
				</div>
			</div>
			
			<div class="mt-5" v-if="showChangeFlow">
				<div class="row">
					<div class="col">
						<h4>Ingresar artículos que se devuelven</h4>
					</div>
				</div>
				<div class="form-row mb-4 mt-4">
					<div class="col-3">
						<div>
							<label for="article">Artículo</label>
							<input @keydown.tab="disableTab" ref="articleForChangeInput" id="articleForChangeInput" type="text" class="form-control"  v-model="barcodeForChange" @change="getDetailItem(barcodeForChange, true)">
						</div>
					</div>
				</div>
				<hr />

				<table class="table" v-if="itemsForChangeList.length">
					<thead>
						<tr>
							<th scope="col">Código</th>
							<th scope="col">Descripción</th>
							<th scope="col">Precio</th>
							<th scope="col"></th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="(item, index) in itemsForChangeList" :key="'item-change-'+index">
							<td>{{ item.barcode }}</td>
							<td>{{ getDescription(item) }}</td>
							<td>${{ item.sell_price.toLocaleString(2) }}</td>
							<td><button type="button" class="boton" @click="removeItemForChange(item, index)">Borrar</button></td>
						</tr>
					</tbody>
				</table>
			</div>

			<div v-if="items.length" class="row">
				<div class="col-md-6 mt-3">
					<div v-if="amountToPay >= 0">
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
					<div class="mt-3" v-if="itemsForChangeList.length > 0">
						<h3>Cambios: <strong>-${{ subtotalForChange.toLocaleString() }}</strong></h3>
					</div>
					<hr />
					<h3 v-if="orderDiscount && orderDiscount.amount > 0">Descuento: <strong> % {{ orderDiscount.amount }} </strong></h3>
					<div>
						<h3>Recargo: <strong> ${{ getCharge }}</strong></h3>
					</div>
					<hr>
					<h1 style="color:red">Total: <strong>${{ total.toLocaleString() }}</strong></h1>
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
				<button v-if="amountToPay <= 0 || (paymentMethod && (amountToPay - paymentMethod.amount <= 0))" ref="sendOrder" type="button" class="boton" @keydown.esc="$refs.comments.focus()" @click="checkIsDiscount">Confirmar</button>
				<button type="button" class="boton" @keydown.esc="$refs.sendOrder.focus()" @keydown.tab="$event.preventDefault()" @click="activeDeleteDialog = true">Borrar</button>
			</div>
		</form>

		<md-dialog-confirm
			:md-active.sync="activeDeleteDialog"
			md-title="¿Seguro querés borrar cancelar esta orden?"
			md-confirm-text="Dale mecha"
			md-cancel-text="Cancelar"
			@md-confirm="sendResetNotification" />

		<md-dialog-confirm
			:md-active.sync="showCreateCreditNote"
			:md-title="'¿Desea crear una nota de crédito por $'+Math.abs(amountToPay)+'?'"
			md-confirm-text="Crear Nota de Crédito"
			md-cancel-text="Cancelar"
			@md-confirm="createCreditNote" />

		<md-dialog-confirm v-if="creditNote"
			:md-active.sync="showCreateCreditNoteSuccess"
			:md-title="'Crear una nota de crédito con el número: #'+creditNote.id+' y el monto $'+Math.abs(creditNote.amount)"
			md-confirm-text="Ok"
			@md-cancel="resetOrder"
			@md-confirm="resetOrder" />

		<md-snackbar :md-position="'center'" :md-duration="5000" :md-active.sync="alertSuccessActive" md-persistent>
			<span>{{ alertTitle }}. {{ alertContent }}</span>
		</md-snackbar>

		<md-dialog-alert
			:md-active.sync="alertErrorActive"
			:md-title="alertTitle"
			:md-content="alertContent"
			@md-closed="resetOrder" />

		<md-dialog :md-active.sync="showDiscountDialog">
            <md-dialog-title>¿Por qué hubo un descuento?</md-dialog-title>
            <div class="form-group p-1">
                <label for="inputPassword4">Comentario</label>
                <input required ref="discountComment" v-model='discountComment' type="text" class="form-control">
            </div>
            <md-dialog-actions>
                <md-button class="md-primary" @click="sendOrder">Enviar</md-button>
            </md-dialog-actions>
        </md-dialog>

	</div>

</template>
<script>
import shoe_searcher from '../utils/shoe_searcher.vue'
	export default {
  		components: { shoe_searcher },
		 name: 'NewOrderForm',
		 data() {
			 return {
				paidMoney: null,
				barcode: null,
				barcodeForChange: null,
				seller: null,
				cashier: null,
				items: [],
				orderDiscount: null,
				newItemDescription: null,
				subtotal: 0,
				subtotalForChange: 0,
				discount: 0,
				paymentMethod: null,
				paymentMethods: [],
				comment: null,
				showDiscountDialog: false,
				discountComment: null,
				discountItems: 0,
				itemsForChangeList: [],

				notFoundItemMessage: false,
				activeDeleteDialog: false,

				alertSuccessActive: false,
				alertErrorActive: false,
				alertTitle: null,
				alertContent: null,

				showCreateCreditNote: false,
				showCreateCreditNoteSuccess: false,
				showChangeFlow: false,

				creditNote: null,
			 }
		 },
		 computed: {
			 amountToPay() {
				var amountToPay = this.subtotal - this.subtotalForChange
				if (this.orderDiscount)
					amountToPay = amountToPay * (1 - this.orderDiscount.amount / 100)
				this.paymentMethods.forEach(element => {
					if (element && element.option)
						amountToPay = amountToPay - ( element.amount / (1 + element.option.charge / 100))
					else if (element && element.amount)
						amountToPay = amountToPay - element.amount
				})
				return amountToPay
			 },
			 total() {
				let subtotal = Number(this.subtotal)
				let subtotalForChange = Number(this.subtotalForChange)
				if (this.orderDiscount)
					subtotal = subtotal * (1 - this.orderDiscount.amount / 100)

				return subtotal + this.getCharge - subtotalForChange
			 },
			 change() {
				 return (this.paidMoney - this.total).toLocaleString()
			 },
			 getCharge() {
				 var charge = 0
				 this.paymentMethods.forEach(element => {
					if (element && element.option)
						charge = charge + ( element.amount * element.option.charge / 100)
				 })
				 if (this.paymentMethod && this.paymentMethod.option)
				 	charge = charge + ( this.paymentMethod.amount * this.paymentMethod.option.charge / 100)
				 return charge
			 }
		 },
		 methods: {
			activateShowChangeFlow() {
				this.showChangeFlow = true
				this.$nextTick(() => {
					this.$refs.articleForChangeInput.focus()
				}); 
			},
			removeItemForChange(item, index) {
				this.itemsForChangeList.splice(index, 1)
				this.subtotalForChange -= item.sell_price
			},
			getDescription(item) {
				return `${item.brand_name} - ${item.code} - ${item.color} - Nro: ${item.number}`.toUpperCase()
			},
			confirmItem(sell_price, price, discount, subtotal) {
				this.newItemDescription.discount = discount
				this.newItemDescription.subtotal = subtotal
				this.newItemDescription.price = price
				this.newItemDescription.sell_price = sell_price
				this.newItemDescription.qty = 1
				this.items.unshift(this.newItemDescription)
				this.subtotal += this.newItemDescription.subtotal

				if (sell_price > price) {
					this.discountItems += sell_price - subtotal
				}

				this.newItemDescription = null
				this.$refs.article.focus()
			},
			disableTab(e) {
				if (this.barcode != null || (this.items.length === 0 && this.newItemDescription == null) )
					e.preventDefault()
			}, 
			moveTo(ref) {
				this.$refs[ref].focus()
			},
			moveTo(parent, child) {
				this.$refs[parent].$refs[child].focus()
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
				this.$refs.paymentMethodSelector.$refs.method.focus()
			},
			updateSeller(value) {
				this.seller = value
				if (value != null)
					this.$refs.cashier.$refs.user.focus()
			},
			updateCashier(value) {
				this.cashier = value
				if (value != null)
					this.$refs.article.focus()
			},
			updateArticle(article) {
				if (article != null) {
					this.barcode = article.barcode
					this.getDetailItem()
				}
				this.$refs.article.focus()
			},
			getDetailItem(barcode, isForChange = false) {
				if (barcode != null) {
					axios.get('/api/stock/detail_item_barcode', {params: { barcode: barcode }})
					.then(response => {
						let data = response.data
						this.barcode = null
						this.barcodeForChange = null
						if (data.item.length !== 0) {
							if (isForChange) {
								this.itemsForChangeList.push(response.data.item)
								this.subtotalForChange += response.data.item.sell_price
							}
							else {
								this.newItemDescription = response.data.item
								this.newItemDescription.qty = 1
							}
							return
						}
						else
							this.newItemDescription = null
					})
					.finally(() => {
						if (isForChange) {
							this.$refs.articleForChangeInput.focus()
						}
						else {
							this.$refs.article.focus()
						}
					})
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
			checkIsDiscount() {
				if (this.discountItems > 0) {
					this.showDiscountDialog = true
				}
				else {
					this.sendOrder()
				}
			},
			sendOrder() {
				axios.post('/api/order', { order: this.createRequest() })
				.then(response => {
					console.log(response.data)
					this.alertTitle = response.data.status
					this.alertContent = response.data.message
					this.alertSuccessActive = true
					if (response.data.statusCode == 200) {
						if (this.itemsForChangeList.length && this.total < 0) {
							this.showCreateCreditNote = true
						}
						else {
							this.resetOrder()
						}
					}
					else {
						this.resetOrder()
					}
				})
			},
			sendResetNotification() {
				axios.post('/api/order/reset', { id_cashier: this.cashier.id, id_seller: this.seller.id })
				.then(response => {
					this.resetOrder()
				})
			},
			createCreditNote() {
				this.showCreateCreditNote = false
				axios.post('/api/creditNote', { amount: Math.abs(this.amountToPay) })
				.then(response => {
					if (response.data.statusCode == 200) {
						this.showCreateCreditNoteSuccess = true
						this.creditNote = response.data.creditNote
					}
					else {
						this.alertErrorActive = true
						this.alertTitle = response.data.status
						this.alertContent = response.data.message
					}
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
				this.showDiscountDialog = false
				this.discountComment = null
				this.showCreateCreditNoteSuccess = false
				this.creditNote = null
				this.showChangeFlow = false
				this.itemsForChangeList = []
				this.subtotalForChange = 0

				this.$refs.seller.$refs.user.focus()
			},
			createRequest() {
				let data = {}
				data.seller = this.seller.id
				data.cashier = this.cashier.id
				//TODO cambiar esto en futuro para agrupar items iguales
				data.qty = this.items.length + this.itemsForChangeList.length
				data.items = this.items.map(item => ({
					id: item.id,
					qty: item.qty,
					buy_price: item.buy_price,
					sell_price: item.sell_price,
					price: item.price
				}))
				data.itemsForChange = this.itemsForChangeList.map(item => ({
					id: item.id,
					qty: 1,
					buy_price: item.buy_price,
					sell_price: item.sell_price,
					price: item.sell_price
				}))
				if (this.itemsForChangeList.length)
					data.orderType = 'OrderChange'
				else
					data.orderType = 'OrderSucursal'
				data.orderDiscount = this.orderDiscount
				data.discountItems = this.discountItems
				data.subtotal = parseFloat(this.subtotal)
				data.total = parseFloat(this.total)
				data.discount = this.discount
				//add payment methods
				data.paymentMethods = this.paymentMethods
				//Append lastone
				if (this.paymentMethod)
					data.paymentMethods.push(this.paymentMethod)
				data.comment = this.comment
				if (this.discountItems > 0)
					data.comment += `. Descuento: ${this.discountComment}`
				
				return data
			}
		},
		mounted() {
			this.$refs.seller.$refs.user.focus()	
		}
	}
</script>
<style scoped>
	.boton:focus {
		background: black;
		color: white;
	}
</style>