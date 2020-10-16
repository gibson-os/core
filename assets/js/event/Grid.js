Ext.define('GibsonOS.module.core.event.Grid', {
    extend: 'GibsonOS.core.component.grid.Panel',
    alias: ['widget.gosModuleCoreEventGrid'],
    autoScroll: true,
    initComponent: function () {
        let me = this;

        me.store = new GibsonOS.module.core.event.store.Grid();

        me.callParent();

        me.addAction({
            xtype: 'tbseparator',
            addToContainerContextMenu: false,
        });
        me.addAction({
            iconCls: 'icon_system system_play',
            disabled: true,
            text: 'Ausführen',
            addToContainerContextMenu: false,
        });

        me.on('itemdblclick', function(view, record) {
            let window = new GibsonOS.module.core.event.Window({
                gos: {
                    data: {
                        eventId: record.get('id')
                    }
                }
            });

            window.down('gosModuleCoreEventForm').loadRecord(record);
        });
    },
    addFunction: function() {
        let me = this;

        new GibsonOS.module.core.event.Window({
            gos: me.gos
        });
    },
    deleteFunction: function() {
        console.log('delete');
    },
    getColumns: function() {
        return [{
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
    }
});