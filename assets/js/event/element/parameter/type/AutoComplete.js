Ext.define('GibsonOS.module.core.event.element.parameter.type.AutoComplete', {
    extend: 'GibsonOS.form.AutoComplete',
    alias: ['widget.gosModuleCoreEventElementParameterTypeAutoComplete'],
    model: 'GibsonOS.module.core.event.model.Slave',
    initComponent: function () {
        let me = this;

        me.url = baseDir + me.gos.data.url;
        me.model = me.gos.data.model;
        me.params = me.gos.data.parameters;

        me.callParent();

        me.on('afterrender', function() {
            if (!me.gos.data.listeners) {
                return;
            }

            Ext.iterate(me.gos.data.listeners, function(fieldName, options) {
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