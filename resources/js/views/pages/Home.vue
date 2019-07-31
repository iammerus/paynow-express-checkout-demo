<template>
    <v-card class="elevation-12">
        <v-toolbar color="red darken-1" dark flat>
            <v-toolbar-title>Store</v-toolbar-title>
            <v-spacer></v-spacer>

            <v-tooltip right>
                <template v-slot:activator="{ on }">
                    <v-badge class="align-self-center badge-nav" color="red lighten-3" :overlap="true" >
                        <template v-slot:badge>
                            <span>{{ cartTotalCount }}</span>
                        </template>
                        <v-btn to="/checkout" icon large v-on="on">
                            <v-icon>mdi-cart</v-icon>
                        </v-btn>
                    </v-badge>
                </template>
                <span>Checkout ({{ cartTotalCount }} items in cart)</span>
            </v-tooltip>
        </v-toolbar>
        <v-card-text>
            <v-list class="foods-list" :max-height="500" :three-line="true" :flat="true" :nav="true" :avatar="true">
                <v-list-item-group v-model="food" color="red lighten-1">
                    <v-list-item
                            v-for="(food, i) in foods"
                            :key="i"
                    >
                        <v-list-item-avatar>
                            <v-img :src="food.image_url"></v-img>
                        </v-list-item-avatar>

                        <v-list-item-content>
                            <v-list-item-title v-html="food.title"></v-list-item-title>
                            <v-list-item-subtitle v-html="food.description"></v-list-item-subtitle>
                        </v-list-item-content>

                        <v-list-item-action>
                            <v-tooltip left>
                                <template v-slot:activator="{ on }">
                                    <v-btn @click="addToCart(food)" icon medium>
                                        <v-icon color="red darken-2">mdi-cart-plus</v-icon>
                                    </v-btn>
                                </template>
                                <span>Back to home</span>
                            </v-tooltip>

                        </v-list-item-action>
                    </v-list-item>
                </v-list-item-group>
            </v-list>
        </v-card-text>
        <v-card-actions>
            <v-spacer></v-spacer>
        </v-card-actions>
    </v-card>
</template>

<script>
    export default {
        name: "Home",
        props: {
            foods: Array
        },
        data: () => ({
            food: 5
        }),
        methods: {
            addToCart(foodItem) {
                try {
                    this.insertToCart(foodItem);

                    this.showSnackbar("Item was added to cart successfully!", 3500);
                } catch (error) {
                    console.log(error);

                    this.showSnackbar("An error occurred while adding the item to cart", 3500);
                }
            }
        }
    }
</script>

<style lang="scss" scoped>
    .foods-list {
        overflow-y: scroll;

        &::-webkit-scrollbar-track {
            -webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3);
            background-color: #F5F5F5;
        }

        &::-webkit-scrollbar {
            width: 5px;
            background-color: #F5F5F5;
        }

        &::-webkit-scrollbar-thumb {
            background-color: #000000;
            border: 2px solid #555555;
        }
    }
    .badge-nav .v-badge__badge {
        margin-top: 5px;
    }

</style>