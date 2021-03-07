Ext.define('GibsonOS.module.core.event.element.parameter.Window', {
    extend: 'GibsonOS.Window',
    alias: ['widget.gosModuleCoreEventElementParameterWindow'],
    title: 'Parameter',
    width: 420,
    y: 50,
    autoHeight: true,
    maximizable: true,
    withOperator: false,
    excludeOperators: [],
    requiredPermission: {
        module: 'core',
        task: 'event'
    },
    initComponent: function() {
        let me = this;

        me.items = [{
            xtype: 'gosModuleCoreEventElementParameterForm',
            withOperator: me.withOperator
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
                parameterObject: parameter,
                fieldLabel: parameter.title
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
                case 'date':
                    item.xtype = 'gosCoreComponentFormFieldDate';
                    break;
                case 'time':
                    item.xtype = 'gosCoreComponentFormFieldTime';
                    break;
                case 'dateTime':
                    item.xtype = 'gosCoreComponentFormFieldDateTime';
                    break;
                case 'option':
                    break;
            }

            if (me.withOperator) {
                form.add({
                    xtype: 'gosCoreComponentFormFieldContainer',
                    fieldLabel: parameter.title,
                    items: [{
                        xtype: 'gosModuleCoreEventElementOperatorComboBox',
                        name: name + 'Operator',
                        margins: '0 5px 0 0',
                        value: parameter.operator ?? null,
                        exclude: me.excludeOperators
                    }, item]
                });
            } else {
                form.add(item);
            }
        });
    }
});