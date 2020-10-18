Ext.define('GibsonOS.module.core.cronjob.Grid', {
    extend: 'GibsonOS.core.component.grid.Panel',
    alias: ['widget.gosModuleCoreCronjobGrid'],
    itemId: 'coreCronjobGrid',
    initComponent: function() {
        let me = this;

        me.store = new GibsonOS.module.core.cronjob.store.Cronjob();

        me.callParent();
    },
    addFunction: function() {
        new GibsonOS.module.core.cronjob.Window();
    },
    enterFunction: function(record) {
        let window = new GibsonOS.module.core.cronjob.Window();
        window.down('gosModuleCoreCronjobForm').loadRecord(record);

        let timeStore = window.down('gosModuleCoreCronjobTimeGrid').getStore();
        timeStore.getProxy().setExtraParam('cronjobId', cronjob.id);
        timeStore.load();
    },
    getColumns: function() {
        return [{
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
    }
});