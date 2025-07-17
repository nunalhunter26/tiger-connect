import template from './sw-order-list.html.twig'

export default {
    template,

    computed: {
        listFilterOptions() {
            return {
                ['tiger-connect-status-filter']: {
                    property: 'customFields.tiger_connect_custom_field_set_processed',
                    type: 'existence-filter',
                    label: 'TigerConnect Status',
                    placeholder: 'Select status...',
                    optionHasCriteria: 'Success',
                    optionNoCriteria: 'Failed',
                    optionNullCriteria: 'N/A'
                },
                ...this.$super('listFilterOptions')
            };
        }
    },

    created() {
        if (!this.defaultFilters.includes('tiger-connect-status-filter')) {
            this.defaultFilters.push('tiger-connect-status-filter');
        }
    },

    methods: {
        getOrderColumns() {
            const columns = this.$super('getOrderColumns');
            columns.push({
                property: 'customFields.tiger_connect_custom_field_set_processed',
                label: 'TigerConnect Status'
            })

            return columns;
        },

        getVariant(status) {
            switch (status) {
                case true: return 'success';
                case false: return 'danger';
                default: return 'warning';
            }
        },

        getLabel(status) {
            switch (status) {
                case true: return this.$tc('order-list.labelSuccess');
                case false: return this.$tc('order-list.labelFailed');
                default: return this.$tc('order-list.labelNull');
            }
        }
    }
}