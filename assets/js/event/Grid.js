Ext.define('GibsonOS.module.core.event.Grid', {
    extend: 'GibsonOS.grid.Panel',
    alias: ['widget.gosModuleCoreEventGrid'],
    autoScroll: true,
    initComponent: function () {
        let me = this;

        me.store = new GibsonOS.module.core.event.store.Grid();
        me.columns = [{
            header: 'Name',
            dataIndex: 'name',
            flex: 1,
            editor: {
                allowBlank: false
            }
        },{
            header: 'Trigger',
            dataIndex: 'triggers',
            flex: 1,
            renderer: function(value) {
                return value;
            }
        },{
            xtype: 'booleancolumn',
            header: 'Aktiv',
            dataIndex: 'active',
            trueText: 'Ja',
            falseText: 'Nein',
            width: 50
        }];

        me.tbar = [{
            xtype: 'gosButton',
            iconCls: 'icon_system system_add',
            requiredPermission: {
                action: 'save',
                permission: GibsonOS.Permission.WRITE
            },
            handler: function () {
                new GibsonOS.module.core.event.Window({
                    gos: me.gos
                });
            }
        },{
            xtype: 'gosButton',
            iconCls: 'icon_system system_delete',
            disabled: true,
            requiredPermission: {
                action: 'saveToEeprom',
                permission: GibsonOS.Permission.DELETE
            },
            handler: function() {
            }
        }];

        me.callParent();

        me.on('itemdblclick', function(view, record) {
            new GibsonOS.module.core.event.Window({
                gos: {
                    data: {
                        eventId: record.get('id')
                    }
                }
            });
        });
    }
});