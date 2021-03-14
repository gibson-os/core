Ext.define('GibsonOS.module.core.parameter.Window', {
    extend: 'GibsonOS.Window',
    alias: ['widget.gosModuleCoreParameterWindow'],
    title: 'Parameter',
    width: 420,
    y: 50,
    autoHeight: true,
    maximizable: true,
    withOperator: false,
    withSet: false,
    requiredPermission: {
        module: 'core',
        task: 'event'
    },
    initComponent: function() {
        let me = this;

        me.items = [{
            xtype: 'gosModuleCoreParameterForm',
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
        let form = me.down('gosModuleCoreParameterForm');

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
                    item.inputType = parameter.config.inputType;
                    break;
                case 'int':
                    item.xtype = 'gosFormNumberfield';
                    break;
                case 'bool':
                    item.xtype = 'gosFormCheckbox';
                    break;
                case 'autoComplete':
                    item.xtype = 'gosModuleCoreParameterTypeAutoComplete';
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
                if (me.withSet) {
                    parameter.allowedOperators.push('=');
                }

                form.add({
                    xtype: 'gosCoreComponentFormFieldContainer',
                    fieldLabel: parameter.title,
                    items: [{
                        xtype: 'gosModuleCoreEventElementOperatorComboBox',
                        name: name + 'Operator',
                        margins: '0 5px 0 0',
                        value: parameter.operator ?? null,
                        allowed: parameter.allowedOperators
                    }, item]
                });
            } else {
                form.add(item);
            }
        });
    }
});