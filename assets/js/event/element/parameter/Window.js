Ext.define('GibsonOS.module.core.event.element.parameter.Window', {
    extend: 'GibsonOS.Window',
    alias: ['widget.gosModuleCoreEventElementParameterWindow'],
    title: 'Parameter',
    width: 400,
    autoHeight: true,
    maximizable: true,
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
        let me = this;
        let form = me.down('gosModuleCoreEventElementParameterForm');

        Ext.iterate(parameters, function(name, parameter) {
            let item = {
                name: name,
                fieldLabel: parameter.title,
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

            form.add(item);
        });
    }
});