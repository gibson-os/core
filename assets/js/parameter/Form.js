Ext.define('GibsonOS.module.core.parameter.Form', {
    extend: 'GibsonOS.form.Panel',
    alias: ['widget.gosModuleCoreParameterForm'],
    border: false,
    items: [],
    withOperator: false,
    initComponent() {
        const me = this;
        
        me.buttons = [{
            text: 'Speichern',
            itemId: 'coreEventElementParameterSaveButton',
            listeners: {
                click: function() {
                    me.items.each(function(field) {
                        if (me.withOperator) {
                            field.items.items[1].parameterObject.operator = field.items.items[0].getValue();
                            field.items.items[1].parameterObject.value = field.items.items[1].getValue();

                            return true;
                        }

                        field.parameterObject.value = field.getValue();
                    });
                }
            }
        }];

        me.callParent();
    },
});