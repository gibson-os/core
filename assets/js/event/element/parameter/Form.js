Ext.define('GibsonOS.module.core.event.element.parameter.Form', {
    extend: 'GibsonOS.form.Panel',
    alias: ['widget.gosModuleCoreEventElementParameterForm'],
    border: false,
    items: [],
    initComponent: function() {
        let me = this;
        
        me.buttons = [{
            text: 'Speichern',
            itemId: 'coreEventElementParameterSaveButton',
            listeners: {
                click: function() {
                    me.items.each(function(field) {
                        field.parameterObject.value = field.getValue();
                    });
                }
            }
        }];

        me.callParent();
    },
});