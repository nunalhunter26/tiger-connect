import template from './sw-existence-filter.html.twig';

const { Criteria } = Shopware.Data;

export default {
    template,

    methods: {
        changeValue(newValue) {
            if (!newValue) {
                this.$super('changeValue', newValue);
                return;
            }

            if (this.filter.name !== 'tiger-connect-status-filter') {
                this.$super('changeValue', newValue);
                return;
            }

            let filterCriteria = [Criteria.equals(this.filter.property, null)];

            if (newValue === 'true') {
                filterCriteria = [Criteria.equals(this.filter.property, 1)];
            }

            if (newValue === 'false') {
                filterCriteria = [Criteria.equals(this.filter.property, 0)];
            }

            this.$emit('filter-update', this.filter.name, filterCriteria, newValue);
        }
    }
}