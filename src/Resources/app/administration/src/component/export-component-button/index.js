import template from './export-component-button.html.twig';

const { Component } = Shopware;
const { Mixin } = Shopware;
const { mapState, mapGetters } = Shopware.Component.getComponentHelper();
const { Application } = Shopware;
const { ApiService } = Shopware.Classes;

Component.register('export-component-button', {
    template,

    inject: ['loginService'],

    mixins: [
        Mixin.getByName('notification'),
    ],

    computed: {
        ...mapState('swOrderDetail', [
            'order'
        ]),
        ...mapGetters('swOrderDetail', [
            'isLoading',
        ]),
        isProcessed() {
            return this.order ? this.order.customFields?.tiger_connect_custom_field_set_processed ?? false : true;
        }
    },

    methods: {
        onExport() {
            Shopware.State.commit('swOrderDetail/setLoading', ['states', true]);
            const initContainer = Application.getContainer('init').httpClient;
            let httpClient = new ApiService(initContainer, this.loginService, '_action/tiger-connect');
            const basicHeaders = {
                Accept: 'application/json',
                Authorization: `Bearer ${this.loginService.getToken()}`,
                'Content-Type': 'application/json',
            };

            httpClient.httpClient.post(
                httpClient.getApiBasePath() + `/order/export/${this.order.orderNumber}`,
                {},
                {
                    headers: basicHeaders
                }
            ).then((response) => {
                if (response.data.result === true) {
                    this.$router.go();
                }
            }).catch((error) => {
                let errorDetails = null;

                try {
                    errorDetails = error.response.data.errors[0].detail;
                } catch (e) {
                    errorDetails = this.$tc('tigerConnect.order.errorMsg');
                }

                this.createNotificationError({
                    message: errorDetails,
                });
                Shopware.State.commit('swOrderDetail/setLoading', ['states', false]);
            });
        }
    }
});