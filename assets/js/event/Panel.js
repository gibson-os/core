Ext.define('GibsonOS.module.core.event.Panel', {
    extend: 'GibsonOS.Panel',
    alias: ['widget.gosModuleCoreEventPanel'],
    itemId: 'coreEventPanel',
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
                title: 'Ereignisse',
                gos: me.gos
            },{
                xtype: 'gosModuleCoreEventTriggerGrid',
                title: 'Auslöser',
                gos: me.gos
            }]
        }];

        let getElementsAjaxData = function(element) {
            let data = {
                id: element.get('id'),
                command: element.get('command'),
                className: element.get('className'),
                method: element.get('method'),
                operator: element.get('operator'),
                parameters: {},
                returns: {},
                children: []
            };

            Ext.iterate(element.get('parameters'), function(name, parameter) {
                data.parameters[name] = parameter.value;
            });

            Ext.iterate(element.get('returns'), function(name, parameter) {
                data.returns[name] = parameter.value;
            });

            element.eachChild(function(children) {
                data.children.push(getElementsAjaxData(children));
            });

            return data;
        };

        me.tbar = [{
            xtype: 'gosButton',
            iconCls: 'icon_system system_save',
            requiredPermission: {
                action: 'save',
                permission: GibsonOS.Permission.READ
            },
            handler: function() {
                let form = me.down('gosModuleCoreEventForm').getForm();
                let elements = [];
                let triggers = [];

                me.down('gosModuleCoreEventElementTreeGrid').getStore().getRootNode().eachChild(function(element) {
                    elements.push(getElementsAjaxData(element));
                });

                me.down('gosModuleCoreEventTriggerGrid').getStore().each(function(trigger) {
                    triggers.push(trigger.getData());
                });

                GibsonOS.Ajax.request({
                    url: baseDir + 'core/event/save',
                    params: {
                        eventId: me.gos.data.eventId,
                        name: form.findField('name').getValue(),
                        async: form.findField('async').getValue(),
                        active: form.findField('active').getValue(),
                        elements: Ext.encode(elements),
                        triggers: Ext.encode(triggers)
                    },
                    success: function(response) {
                        me.gos.data.eventId = Ext.decode(response.responseText).data.id;
                    }
                });
            }
        }];

        me.callParent();
    }
});