<template>
    <div class="col-md-12">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <md-list>
                        <md-list-item @click="activateCards(m)" v-for="m in methods" :key="'method-'+m.id">
                            <span class="md-list-item-text">{{ m.name }}</span>
                            <md-button class="md-icon-button" v-if="methodSelected == m">
                                <i class="far fa-trash-alt"></i>
                            </md-button>
                        </md-list-item>
                        <md-divider></md-divider>
                        <md-list-item>Agregar</md-list-item>
                    </md-list>
                </div>
            </div>
            <div class="col-md-4" v-if="activeCards">
                <div class="card">
                    <md-list>
                        <md-list-item @click="getCardsPaymentOptions(card)" v-for="card in cards" :key="'card-'+card.id">
                            <span class="md-list-item-text">{{ card.name }}</span>
                            <md-button class="md-icon-button" v-if="cardSelected == card">
                                <i class="far fa-trash-alt"></i>
                            </md-button>
                        </md-list-item>
                        <md-divider></md-divider>
                        <md-list-item @click="showAddCard = true">Agregar</md-list-item>
                    </md-list>
                </div>
            </div>
            <div class="col-md-4" v-if="activeCardOptions">
                <div class="card">
                    <md-list>
                        <md-list-item  @click="cardOptionSelected = option" v-for="option in cardOptions" :key="'option-'+option.id">
                            <span class="md-list-item-text">{{ optionDesciption(option) }}</span>
                            <md-button class="md-icon-button" v-if="cardOptionSelected == option">
                                <i class="far fa-trash-alt"></i>
                            </md-button>
                        </md-list-item>
                        <md-divider></md-divider>
                        <md-list-item>Agregar</md-list-item>
                    </md-list>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    name: "PaymentMethods",
    data() {
        return {
            methods: [],
            methodSelected: null,
            cardSelected: null,
            activeCards: false,
            activeCardOptions: false,
            cardOptionSelected: null,
            cardOptions: [],
            cards: [],
            showAddCard: false
        }
    },
    methods: {
        getMethods() {
            axios.get('/api/order/payment_methods').then(response => {
                this.methods = response.data;
            });
        },
        activateCards(method) {
            this.methodSelected = method
            if (method.name === 'Tarjeta')
                this.activeCards = true
            else
                this.activeCards = false
            this.activeCardOptions = false
        },
        getCards() {
            axios.get('/api/order/payment_method_cards').then(response => {
                this.cards = response.data;
            });
        },
        getCardsPaymentOptions(card) {
            this.cardSelected = card
            axios.get('/api/order/payment_method_card_options', {params: { id_card: card.id }}).then(response => {
                this.cardOptions = response.data;
                this.activeCardOptions = true
            });
        },
        optionDesciption(option) {
            let plural = ''

            if (option.installments > 1) {
                plural = 's'
            }

            return `${option.installments} cuota${plural} con ${option.charge}% de recargo`
        }
    },
    created() {
        this.getMethods()
        this.getCards()
    }
}
</script>
<style scoped>
    .md-list-item-fake-button{
        background: #3490dc;
        color: white !important;
    }
</style>