Ext.define('GibsonOS.module.core.component.form.field.NumberField', {
    extend: 'Ext.form.field.Number',
    alias: ['widget.gosCoreComponentFormFieldNumberField'],
    fieldLabel: 'Number Field',
    minValue: 0,
    anchor: '100%',
    border: false,
    initComponent() {
        const me = this;
        let config = me.parameterObject.config;

        me.callParent();

        me.on('afterrender', () => {
            if (!config.listeners) {
                return;
            }

            Ext.iterate(config.listeners, function(fieldName, options) {
                const listenerField =  me.up('form').getForm().findField(fieldName);

                if (!listenerField) {
                    return true;
                }

                listenerField.on('change', (field, value) => {
                    if (options.multiplier) {
                        me.setValue(value * options.multiplier);
                    }
                });
            });
        });
    }
});
