Ext.define('GibsonOS.module.core.event.element.parameter.type.AutoComplete', {
    extend: 'GibsonOS.form.AutoComplete',
    alias: ['widget.gosModuleCoreEventElementParameterTypeAutoComplete'],
    initComponent: function () {
        let me = this;
        let config = me.parameterObject.config;

        me.url = baseDir + 'core/autoComplete/autoComplete';
        me.model = config.model;
        me.params = config.parameters;
        me.params.autoCompleteClassname = config.autoCompleteClassname;

        me.callParent();

        me.on('afterrender', function() {
            if (!config.listeners) {
                return;
            }

            Ext.iterate(config.listeners, function(fieldName, options) {
                me.up('form').getForm().findField(fieldName).on('change', function(field, value) {
                    let record = field.findRecordByValue(value);

                    Ext.iterate(options, function(property, option) {
                        if (property === 'params') {
                            me.getStore().getProxy().setExtraParam(option.paramKey, record.get(option.recordKey));
                            me.setValueById(null);
                        }
                    })
                });
            });
        });
    }
});