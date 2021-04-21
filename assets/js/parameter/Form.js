Ext.define('GibsonOS.module.core.parameter.Form', {
    extend: 'GibsonOS.module.core.component.form.Panel',
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
    addField(name, parameter) {
        const me = this;
        let item = {
            xtype: parameter.xtype,
            name: name,
            value: parameter.value ?? null,
            parameterObject: parameter,
            fieldLabel: parameter.title
        };

        if (parameter.config.inputType) {
            item.inputType = parameter.config.inputType;
        }

        if (parameter.config.options) {
            item.store = {
                fields: ['id', 'name'],
                data: []
            }

            Ext.iterate(parameter.config.options, (value, id) => {
                item.store.data.push({
                    id: id,
                    name: value
                });
            });
        }

        if (me.withOperator) {
            if (me.withSet) {
                parameter.allowedOperators.push('=');
            }

            me.add({
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
            me.add(item);
        }
    },
    addFields(parameters) {
        const me = this;

        me.fireEvent('beforeAddFields', parameters);

        Ext.iterate(parameters, function(name, parameter) {
            me.addField(name, parameter)
        });

        me.fireEvent('afterAddFields', parameters);
    }
});