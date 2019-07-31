<template>
    <div>
        <v-card class="elevation-12">
            <v-toolbar color="red darken-1" dark flat>
                <v-toolbar-title>Checkout</v-toolbar-title>
                <v-spacer></v-spacer>

                <v-tooltip left>
                    <template v-slot:activator="{ on }">
                        <v-btn to="/" icon large v-on="on">
                            <v-icon>mdi-home</v-icon>
                        </v-btn>
                    </template>
                    <span>Back to home</span>
                </v-tooltip>
            </v-toolbar>
            <v-card-text>
                <template>
                    <v-data-table
                            :headers="tableHeaders"
                            :items="cartItems"
                            :items-per-page="5"
                            item-key="id"
                    >

                        <template v-slot:body.append="{ headers }">
                            <tr>
                                <td>Total:</td>
                                <td class="text-md-right text-sm-left" :colspan="headers.length - 1">
                                    RTGS {{ '$' + cartTotal }}
                                </td>
                            </tr>
                        </template>
                    </v-data-table>
                </template>
            </v-card-text>
            <v-card-actions>
                <!--<v-btn align="right" :disabled="cartTotalCount === 0" class="ma-2 text-md-right" outlined color="red darken-1">Express Payment Using Paynow</v-btn>-->

                <v-layout row wrap justify-end>
                    <v-flex shrink>
                        <v-btn align="right" @click="beginPayment" :disabled="cartTotalCount === 0"
                               class="ma-2 text-md-right" outlined color="indigo">Express Payment Using Paynow
                        </v-btn>
                    </v-flex>
                </v-layout>

                <v-spacer></v-spacer>
            </v-card-actions>
        </v-card>

        <v-layout justify-center>
            <v-dialog v-model="dialog" persistent max-width="600px">
                <v-card>
                    <v-card-title>
                        <span class="headline">Finish Checkout</span>
                    </v-card-title>
                    <v-card-text>
                        <v-form v-model="valid">

                            <v-container grid-list-md>
                                <v-layout wrap>
                                    <v-flex xs12>
                                        <v-alert :type="message.type" v-model="showAlert" border="left"
                                                 close-text="Close Alert" dark dismissible>
                                            {{ message.title }} <br> <br> <span>Reason: </span> {{ message.body }}
                                        </v-alert>
                                    </v-flex>
                                    <v-flex xs12>
                                        <v-text-field :rules="rules.email" v-model="user.email" label="Email Address*"
                                                      type="email"
                                                      required></v-text-field>
                                    </v-flex>
                                    <v-flex xs12>
                                        <v-text-field :rules="rules.phone" :counter="10" v-model="user.phone"
                                                      label="Ecocash Phone Number*" type="text"
                                                      required></v-text-field>
                                    </v-flex>

                                </v-layout>
                            </v-container>
                        </v-form>

                        <small>*indicates required field</small>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="blue darken-1" text @click="dialog = false">Cancel</v-btn>
                        <v-btn color="blue darken-1" text @click="makePayment">Make Payment</v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-layout>

        <v-overlay :opacity="0.9" :z-index="Number.MAX_SAFE_INTEGER" :value="loading">
            <div class="text-center">
                <span v-html="loaderMessage"></span>
            </div>
            <v-progress-circular indeterminate size="64"></v-progress-circular>
        </v-overlay>
    </div>
</template>

<script>
    export default {
        name: "Checkout",
        data: () => ({
            loading: false,
            showAlert: false,
            message: {},
            valid: true,
            dialog: false,
            tableHeaders: [
                {text: 'Title', value: 'title'},
                {text: 'Description', value: 'description'},
                {text: 'Unit Price (RTGS $)', value: 'price'},
                {text: 'Quantity', value: 'quantity'},
                {text: 'Subtotal', value: 'subtotal'},
            ],
            user: {},
            rules: {
                phone: [
                    v => !!v || 'Phone is required',
                    v => (v ? v.length === 10 : false) || 'Phone number must have 10 characters',
                    v => /07[7,8]\d{7}/.test(v) || "Please enter a valid Econet number"
                ],
                email: [
                    v => !!v || 'E-mail is required',
                    v => /.+@.+/.test(v) || 'E-mail must be valid',
                ],
            },
            loaderMessage: ""
        }),
        props: {
            foods: Array
        },
        methods: {
            beginPayment() {
                this.dialog = true;
            },
            async makePayment() {
                if (!this.valid) {
                    return this.showSnackbar('Please check your input before proceeding');
                }

                const data = new FormData();

                data.append('amount', this.cartTotal);
                data.append('phone', this.user.phone);
                data.append('email', this.user.email);

                const response = (await axios.post('/api/v1/payment/initiate', data)).data;

                if (response.status === 'error') {
                    this.message = {
                        type: 'error',
                        title: response.message,
                        body: response.reason
                    };

                    this.showAlert = true;

                    return;
                }

                let parts = response.message.split('\n');
                let message = "";

                parts.forEach((part) => {
                    message += part + '<br>'
                });

                let transaction = response.transaction;

                this.loaderMessage = message;
                this.loading = true;
                console.log(response);

                var poll = new FormData();
                poll.append('transaction', transaction.id);
                var retries = 20;

                this.callbackHandler = setInterval(async () => {
                    const response = (await axios.post('/api/v1/payment/poll', poll)).data;

                    if (response.status.toLowerCase() === 'paid') {
                        return this.markAsPaid();
                    }

                    if (retries === 1) {
                        return this.markAsFailed();
                    }

                    retries--;
                }, 2000);
            },
            /**
             * Remove the interval from the page
             *
             * @return {void}
             */
            removeCallback() {
                clearInterval(this.callbackHandler);
            },

            /**
             * Execute actions for a failed transaction
             *
             * @return {void}
             */
            markAsFailed() {
                this.removeCallback();

                this.removeOverlay();

                this.showSnackbar("Transaction timed out while waiting for payment.");
            },

            /**
             * Dismiss the overlay component
             *
             * @return {void}
             */
            removeOverlay() {
                this.loading = false;
            },

            /**
             * Execute actions for a paid transaction
             *
             * @return {void}
             */
            markAsPaid() {
                // Remove interval
                this.removeCallback();

                // Clear cart
                this.saveCart({});

                this.removeOverlay();

                this.user = {};

                // Show a message sucking the user off
                this.showSnackbar("We have successfully received your payment. Thank you for doing business with us");
            }
        },

    }
</script>

<style scoped>

</style>