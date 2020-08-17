<template>
    <div>
        <label>Método de pago</label>
        <select ref="method" @keydown.tab="changeSelected" v-model="selected" class="custom-select"  @change="selectedOption = null">
            <option v-for="i in options" :key="i.id" :value="i">{{ i.name }}</option>
        </select>
        <div v-if="selected &&  selected.name == 'Tarjeta'">
            <div class="mt-3" v-if="selected.cards != null">
                <label>Tipo</label>
                <select ref="card" @change="selectedOption = null" @keydown.tab="disableTab($event, selectedCard)" @keydown.esc="$refs.method.focus()" v-model="selectedCard" class="custom-select">
                    <option v-for="card in selected.cards" :key="'card-'+card.id" :value="card">{{ card.name }}</option>
                </select>
            </div>
            <div class="mt-3" v-if="selectedCard && selectedCard.options != null">
                <label>Cuotas</label>
                <select ref="option" @keydown.tab="disableTab($event, selectedOption)" @keydown.esc="$refs.card.focus()" v-model="selectedOption" class="custom-select">
                    <option v-for="option in selectedCard.options" :key="'option-'+option.id" :value="option">{{ optionDesciption(option) }}</option>
                </select>
            </div>
        </div>
        <div class="mt-3">
            <label>Monto</label>
            <input @keydown.tab="checkPay($event)" ref="amount" type="number" v-model.number="amount" step="0.01">
        </div>
    </div>
</template>
<script>
export default {
    name: 'PaymentMethodSelector',
    data() {
        return {
            selected: null,
            selectedCard: null,
            selectedOption: null,
            options: [],
            amount: this.roundFloat(this.amountToPay)
        }
    },
    watch: {
        selectedOption: function () {
            if (this.selectedOption)
                this.amount = this.roundFloat((this.amountToPay * (1 + this.selectedOption.charge / 100)))
            else
                this.amount = this.roundFloat(this.amountToPay)
        },
        amountToPay(newValue) {
            this.amount = this.roundFloat(newValue)
        } 
    },
    props: {
        amountToPay: {
            type: Number,
            default: null
        }
    },
    methods: {
        getData() {
            axios.get('/api/order/payment_methods')
            .then(response => {
                this.options = response.data;
            });
        },
        changeSelected(e) {
            if (this.selected == null)
                e.preventDefault()
        },
        optionDesciption(option) {
            let plural = ''

            if (option.installments > 1) {
                plural = 's'
            }
            let installment = (this.amount / option.installments).toLocaleString()
            return `${option.installments} cuota${plural} de $${installment} con ${option.charge}% de recargo ($${this.amount})`
        },
        disableTab(e, param) {
            if (param == null)
                e.preventDefault()
        },
        checkPay(e) {
            e.preventDefault()

            let amountToPay = this.selectedOption != null ? this.amountToPay * (1 + this.selectedOption.charge / 100) : this.amountToPay
            
            if (this.roundFloat(amountToPay) > this.roundFloat(parseFloat(this.amount))) {
                this.$emit('update', { method: this.selected, card: this.selectedCard, option: this.selectedOption, amount: this.amount }, true )
                this.selected = null
                this.selectedCard = null
                this.selectedOption = null
                this.amount = amountToPay - this.amount
                this.$refs.method.focus()
            }
            else {
                this.$emit('update', { method: this.selected, card: this.selectedCard, option: this.selectedOption, amount: this.amount }, false )
            }
        },
        roundFloat(number) {
            return Math.round((number + Number.EPSILON) * 100) / 100
        } 
    },
    created() {
        this.getData()
    }

}
</script>