import template from './line-item-type-selection.html.twig';

export default {
    template,

    props: {
        value: {
            type: Array,
            required: true,
            default: []
        }
    },

    data() {
        return {
            lineItemTypes: this.value ?? []
        };
    },

    methods: {
        onChange(item) {
            this.$emit('update:value', item);
            this.$emit('change', item);
        }
    }
}