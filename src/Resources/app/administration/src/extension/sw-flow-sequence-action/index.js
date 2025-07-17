import { ACTION, GROUP } from '../../constant/order-export-action.constant'

const { Component } = Shopware;

Component.override('sw-flow-sequence-action', {
    methods: {
        openDynamicModal(value) {
            if (value === ACTION.ORDER_EXPORT) {
                this.addAction({
                    name: ACTION.ORDER_EXPORT,
                    config: {},
                });
                return;
            }

            return this.$super('openDynamicModal', value);
        },

        getActionTitle(actionName) {
            if (actionName === ACTION.ORDER_EXPORT) {
                return {
                    value: actionName,
                    icon: 'default-badge-help',
                    label: this.$tc('order-export-action.titleOrderExport'),
                    group: GROUP,
                }
            }

            return this.$super('getActionTitle', actionName);
        },

        getActionDescriptions(sequence) {
            if(sequence.actionName === ACTION.ORDER_EXPORT){
                return this.getExportOrderDescription(sequence.config)
            }

            return this.$super('getActionDescriptions', sequence);
        },

        getExportOrderDescription(config) {
            return this.$tc('order-export-action.descriptionTags', 0, {});
        }
    }
});