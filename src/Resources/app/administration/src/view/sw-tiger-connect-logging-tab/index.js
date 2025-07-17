import template from './sw-tiger-connect-order-detail-logs.html.twig';

const { Mixin } = Shopware;
const { Criteria } = Shopware.Data;
const { mapState, mapGetters } = Shopware.Component.getComponentHelper();

Shopware.Component.register('sw-tiger-connect-order-detail-logs', {
    template,

    metaInfo() {
        return {
            title: 'Tiger Connect Logs'
        }
    },

    inject: [
        'repositoryFactory'
    ],

    mixins: [
        Mixin.getByName('listing')
    ],

    data: function () {
        return {
            logs: undefined,
            columns: [
                { property: 'createdAt', label: 'Created' },
                { property: 'level', label: 'Level' },
                { property: 'orderNumber', label: 'Order Number' },
                { property: 'message', label: 'Message' }
            ],
            total: 0,
            limit: 25,
            page: 1,
            sortBy: 'createdAt',
            sortDirection: 'DESC',
            naturalSorting: true,
            displayLog: null
        }
    },

    computed: {
        ...mapState('swOrderDetail', [
            'order'
        ]),
        ...mapGetters('swOrderDetail', [
            'isLoading',
        ]),

        loggerRepository() {
            return this.repositoryFactory.create('tiger_connect_logger');
        },

        defaultCriteria() {
            const criteria      = new Criteria(this.page, this.limit);
            this.naturalSorting = this.sortBy === 'message';

            criteria.setTerm(this.term);
            criteria.addSorting(Criteria.sort(this.sortBy, this.sortDirection, this.naturalSorting));
            criteria.addFilter(Criteria.equals('orderNumber', this.order.orderNumber));
            return criteria;
        },

        date() {
            return Shopware.Filter.getByName('date');
        },

        beautify() {
            try {
                return JSON.stringify(JSON.parse(this.displayLog.message), null, 4);
            } catch (e) {
                return this.displayLog.message;
            }
        },

        isJson() {
            try {
                JSON.parse(this.displayLog.message);
                return true;
            } catch (e) {
                return false;
            }
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        async createdComponent() {
            this.loggerRepository.search(this.defaultCriteria, Shopware.Context.api).then((items) => {
                this.total = items.total;
                this.logs = items;
            }).catch(() => {

            });
        },

        async onPageChange(page) {
            this.page = page.page;
            this.limit = page.limit;
            await this.createdComponent();
        },

        showInfoModal(item) {
            this.displayLog = item;
        }
    }
});