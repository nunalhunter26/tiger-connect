import template from './mail-select-config.html.twig';
const { Mixin } = Shopware;

export default {
    template,

    mixins: [
        Mixin.getByName('notification'),
    ],

    props: {
        value: {
            type: Array,
            required: true,
            default: () => []
        }
    },

    data() {
        return {
            mailRecipients: this.value.length <= 0 ? [{firstName: '', lastName: '', email: '', valid: false}] : this.value,
        };
    },

    computed: {
        getColumns() {
            return [
                {
                    type: String,
                    property: 'firstName',
                    label: 'First Name',
                    inlineEdit: 'string',
                },
                {
                    type: String,
                    property: 'lastName',
                    label: 'Last Name',
                    inlineEdit: 'string',
                },
                {
                    type: String,
                    property: 'email',
                    label: 'E-mail',
                    inlineEdit: 'string'
                }
            ];
        }
    },

    created() {
        this.addBlankRow();
    },

    methods: {
        onInlineEditSave(item) {
            this.validate(item);
        },
        onOptionDelete(item) {
            this.mailRecipients = this.mailRecipients.filter(recipient => recipient.email !== item.email);
            this.addBlankRow();
        },
        addBlankRow() {
            if (!this.mailRecipients.some(recipient => {
                return !recipient.email && !recipient.firstName && !recipient.lastName;
            })) {
                this.mailRecipients.push({firstName: '', lastName: '', email: '', valid: false});
            }
        },
        validate(item) {
            if (!this.isValidEmail(item.email)) {
                item.email = '';
                this.createNotificationError({
                    message: this.$tc('Please enter a valid email address.'),
                });
                return;
            }

            if (!item.email || !item.firstName || !item.lastName) {
                this.createNotificationError({
                    message: this.$tc('Please fill all fields.'),
                });
                return;
            }

            const emailExists = this.mailRecipients.some(recipient => recipient !== item && recipient.email === item.email)

            if (emailExists) {
                item.email = '';
                this.createNotificationError({
                    message: this.$tc('Email already exists.'),
                });
                return;
            }

            item.valid = true;
            this.addBlankRow();
        },
        isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        },
        emitChanges() {
            let validRecipients = this.mailRecipients.filter(recipient => recipient.email);
            this.$emit('change', validRecipients);
            this.$emit('update:value', validRecipients);
        }
    },
    watch: {
        mailRecipients() {
            this.emitChanges();
        }
    }
}
