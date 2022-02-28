Ext.define('GibsonOS.module.core.event.Panel', {
    extend: 'GibsonOS.module.core.component.Panel',
    alias: ['widget.gosModuleCoreEventPanel'],
    layout: 'border',
    initComponent: function () {
        let me = this;

        me.items = [{
            xtype: 'gosModuleCoreEventForm',
            region: 'north',
            flex: 0
        },{
            xtype: 'gosTabPanel',
            region: 'center',
            items: [{
                xtype: 'gosModuleCoreEventElementTreeGrid',
                title: 'Ereignisse'
            },{
                xtype: 'gosModuleCoreEventTriggerGrid',
                title: 'Auslöser'
            }]
        }];

        me.addButton = {
            iconCls: 'icon_system system_save',
            requiredPermission: {
                action: 'save',
                permission: GibsonOS.Permission.READ
            }
        };

        me.callParent();

        GibsonOS.event.action.Execute.init(me);
    },
    addFunction: function() {
        let me = this;
        let form = me.down('gosModuleCoreEventForm').getForm();
        let elements = [];
        let triggers = [];

        const getElementsAjaxData = function(element) {
            let data = {
                id: element.get('id'),
                command: element.get('command'),
                'class': element.get('className'),
                method: element.get('method'),
                parameters: {},
                returns: {},
                children: []
            };

            Ext.iterate(element.get('parameters'), function(name, parameter) {
                if (parameter.value === null) {
                    return true;
                }

                data.parameters[name] = parameter.value;
            });

            Ext.iterate(element.get('returns'), function(name, parameter) {
                if (!parameter.value) {
                    return true;
                }

                data.returns[name] = {
                    value: parameter.value,
                    operator: parameter.operator
                };
            });

            element.eachChild(function(children) {
                data.children.push(getElementsAjaxData(children));
            });

            return data;
        };
        const getTriggerAjaxData = (trigger) => {
            let data = {
                id: trigger.get('id'),
                'class': trigger.get('className'),
                trigger: trigger.get('trigger'),
                parameters: {},
                weekday: trigger.get('weekday'),
                day: trigger.get('day'),
                month: trigger.get('month'),
                year: trigger.get('year'),
                hour: trigger.get('hour'),
                minute: trigger.get('minute'),
                second: trigger.get('second')
            };

            Ext.iterate(trigger.get('parameters'), function(name, parameter) {
                if (!parameter.value) {
                    return true;
                }

                data.parameters[name] = {
                    value: parameter.value,
                    operator: parameter.operator
                };
            });

            return data;
        }

        me.down('gosModuleCoreEventElementTreeGrid').getStore().getRootNode().eachChild(function(element) {
            elements.push(getElementsAjaxData(element));
        });

        me.down('gosModuleCoreEventTriggerGrid').getStore().each(function(trigger) {
            triggers.push(getTriggerAjaxData(trigger));
        });

        me.setLoading(true);

        form.submit({
            xtype: 'gosFormActionAction',
            url: baseDir + 'core/event/save',
            params: {
                elements: Ext.encode(elements),
                triggers: Ext.encode(triggers)
            },
            failure() {
                me.setLoading(false);
            },
            success(form, action) {
                me.setLoading(false);
                form.findField('id').setValue(Ext.decode(action.response.responseText).data.id);
                me.up('window').close();
            }
        });
    }
});