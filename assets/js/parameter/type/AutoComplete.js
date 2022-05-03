Ext.define('GibsonOS.module.core.parameter.type.AutoComplete', {
    extend: 'GibsonOS.module.core.component.form.field.AutoComplete',
    alias: ['widget.gosModuleCoreParameterTypeAutoComplete'],
    initComponent: function () {
        let me = this;
        let config = me.parameterObject.config;

        me.url = baseDir + 'core/autoComplete/autoComplete';
        me.model = config.model;
        me.params = config.parameters ?? [];
        me.params.autoCompleteClassname = config.autoCompleteClassname;

        me.callParent();

        me.on('afterrender', function() {
            if (!config.listeners) {
                return;
            }

            Ext.iterate(config.listeners, function(fieldName, options) {
                const listenerField =  me.up('form').getForm().findField(fieldName);

                if (!listenerField) {
                    return true;
                }

                listenerField.on('change', function(field, value) {
                    let record = field.findRecordByValue(value);

                    if (options['params']) {
                        me.getStore().getProxy().setExtraParam(options['params'].paramKey, record.get(options['params'].recordKey));
                        me.setValueById(null);
                    }
                    // Ext.iterate(options, function(property, option) {
                    //     if (property === 'params') {
                    //         me.getStore().getProxy().setExtraParam(option.paramKey, record.get(option.recordKey));
                    //         me.setValueById(null);
                    //     }
                    // });
                });
            });
        });
    }
});