Ext.define('GibsonOS.module.core.event.element.parameter.Window', {
    extend: 'GibsonOS.Window',
    alias: ['widget.gosModuleCoreEventElementParameterWindow'],
    title: 'Parameter',
    width: 435,
    autoHeight: true,
    maximizable: true,
    withOperator: false,
    requiredPermission: {
        module: 'core',
        task: 'event'
    },
    initComponent: function() {
        let me = this;

        me.items = [{
            xtype: 'gosModuleCoreEventElementParameterForm',
        }];

        me.callParent();

        me.down('#coreEventElementParameterSaveButton').on('click', function() {
            me.close();
        }, this, {
            priority: -999
        });
    },
    addFieldsByParameters: function(parameters) {
        const me = this;
        let form = me.down('gosModuleCoreEventElementParameterForm');

        Ext.iterate(parameters, function(name, parameter) {
            let item = {
                name: name,
                value: parameter.value ?? null,
                parameterObject: parameter
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

            if (me.withOperator) {
                item.hideLabel = true;
                form.add({
                    xtype: 'fieldcontainer',
                    layout: 'hbox',
                    fieldLabel: parameter.title,
                    items: [{
                        xtype: 'gosModuleCoreEventElementOperatorComboBox',
                        name: name + 'Operator',
                        margins: '0 5px 0 0',
                    }, item]
                });
            } else {
                item.fieldLabel = parameter.title;
                form.add(item);
            }
        });
    }
});