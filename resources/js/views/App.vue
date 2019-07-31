<template>
    <v-app id="inspire">
        <v-content>
            <v-container fluid fill-height>
                <v-layout align-center justify-center>
                    <v-flex xs12 sm8 md8>
                        <transition name="fade" :name="transitionName" mode="out-in">
                            <keep-alive>
                                <router-view :foods="foods"></router-view>
                            </keep-alive>
                        </transition>
                        <v-snackbar :top="true" :right="true" :timeout="snackBarTimeout" v-model="snackbar">
                            {{ snackBarText }}
                            <v-btn color="pink" text @click="snackbar = false">
                                Close
                            </v-btn>
                        </v-snackbar>
                    </v-flex>
                </v-layout>
            </v-container>
        </v-content>
    </v-app>
</template>
<script>
    const DEFAULT_TRANSITION = 'fade';

    export default {
        props: {
            source: String,
        },
        data: () => ({
            transitionName: DEFAULT_TRANSITION,
            foods: [],
            snackbar: false,
            snackBarText: "",
            snackBarTimeout: 2500
        }),
        async mounted() {
            this.$eventHub.$on('snackbar.show', (text, timeout) => {
                this.snackBarText = text;
                this.snackBarTimeout = timeout;

                this.snackbar = true;
            })
        },
        async created() {
            this.$router.beforeEach((to, from, next) => {
                let transitionName = to.meta.transitionName || from.meta.transitionName;

                if (transitionName === 'slide') {
                    const toDepth = to.path.split('/').length;
                    const fromDepth = from.path.split('/').length;
                    transitionName = toDepth < fromDepth ? 'slide-right' : 'slide-left';
                }

                this.transitionName = transitionName || DEFAULT_TRANSITION;

                next();
            });

            let response = await axios.get('/api/v1/foods');

            this.foods = response.data.data;
        },
    }
</script>