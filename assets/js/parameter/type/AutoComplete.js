Ext.define('GibsonOS.module.core.parameter.type.AutoComplete', {
    extend: 'GibsonOS.module.core.component.form.field.AutoComplete',
    alias: ['widget.gosModuleCoreParameterTypeAutoComplete'],
    initComponent: function () {
        let me = this;
        let config = me.parameterObject.config;

        me.url = baseDir + 'core/autoComplete';
        me.model = config.model;
        me.params = config.parameters ?? [];
        me.params.autoCompleteClassname = config.autoCompleteClassname;
        me.valueField = config.valueField ?? 'id';
        me.displayField = config.displayField ?? 'name';

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
                        me.getStore().load();
                    }
                });

                if (listenerField.getValue()) {
                    let record = listenerField.findRecordByValue(listenerField.getValue());

                    if (!record) {
                        const loadFunction = (store, records) => {
                            record = records[0];
                            me.getStore().getProxy().setExtraParam(options['params'].paramKey, record.get(options['params'].recordKey));
                            me.getStore().load();
                            listenerField.getStore().un('load', loadFunction);
                        };

                        listenerField.getStore().on('load', loadFunction);
                    }
                }
            });
        });
    }
});