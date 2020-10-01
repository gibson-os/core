Ext.define('GibsonOS.module.core.cronjob.Grid', {
    extend: 'GibsonOS.grid.Panel',
    alias: ['widget.gosModuleCoreCronjobGrid'],
    itemId: 'coreCronjobGrid',
    initComponent: function() {
        var me = this;

        me.store = new GibsonOS.module.core.cronjob.store.Cronjob();
        me.columns = [{
            header: 'Kommando',
            dataIndex: 'command',
            flex: 1
        },{
            header: 'Benutzer',
            dataIndex: 'user',
            flex: 1
        },{
            header: 'Argumente',
            dataIndex: 'arguments',
            flex: 1,
            sortable: false
        },{
            header: 'Optionen',
            dataIndex: 'options',
            flex: 1,
            sortable: false
        },{
            header: 'Letzter Lauf',
            dataIndex: 'last_run',
            width: 120
        },{
            header: 'Aktiv',
            dataIndex: 'active',
            width: 50
        }];
        me.tbar = [{
            iconCls: 'icon_system system_add',
            handler: function() {
                new GibsonOS.module.core.cronjob.form.Window().show();
            }
        }];

        me.callParent();

        me.on('itemdblclick', function(grid, record) {
            new GibsonOS.module.core.cronjob.form.Window({
                gos: {
                    data: {
                        cronjob: record.getData()
                    }
                }
            }).show();
        });
    }
});