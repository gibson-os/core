Ext.define('GibsonOS.module.core.event.element.parameter.Form', {
    extend: 'GibsonOS.form.Panel',
    alias: ['widget.gosModuleCoreEventElementParameterForm'],
    border: false,
    initComponent: function () {
        let me = this;

        me.items = [];

        Ext.iterate(me.gos.data, function(name, parameter) {
            let item = {
                name: name,
                fieldLabel: parameter.title,
                value: parameter.value ?? null,
                gos: {
                    data: parameter.config
                }
            };

            switch (parameter.type) {
                case 'string':
                    item.xtype = 'gosFormTextfield';
                    break;
                case 'int':
                    item.xtype = 'gosFormNumberfield';
                    break;
                case 'bool':
                    item.xtype = 'gosFormCheckbox';
                    break;
                case 'autoComplete':
                    item.xtype = 'gosModuleCoreEventElementParameterTypeAutoComplete';
                    break;
                case 'option':
                    break;
            }

            me.items.push(item);
        });
        
        me.buttons = [{
            text: 'Speichern',
            itemId: 'coreEventElementParameterSaveButton',
            listeners: {
                click: function() {
                    Ext.iterate(me.gos.data, function(name, parameter) {
                        let field = me.getForm().findField(name);
                        parameter.value = field.getValue();
                    });
                }
            }
        }];

        me.callParent();
    }
});