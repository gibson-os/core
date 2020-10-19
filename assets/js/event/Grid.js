Ext.define('GibsonOS.module.core.event.Grid', {
    extend: 'GibsonOS.core.component.grid.Panel',
    alias: ['widget.gosModuleCoreEventGrid'],
    autoScroll: true,
    initComponent: function () {
        let me = this;

        me.store = new GibsonOS.module.core.event.store.Grid();

        me.callParent();

        GibsonOS.event.action.Execute.init(me);
    },
    addFunction: function() {
        new GibsonOS.module.core.event.Window();
    },
    enterFunction: function(record) {
        let window = new GibsonOS.module.core.event.Window();
        window.down('gosModuleCoreEventForm').loadRecord(record);

        let elementStore = window.down('gosModuleCoreEventElementTreeGrid').getStore();
        elementStore.getProxy().setExtraParam('eventId', record.get('id'));
        elementStore.load();

        let triggerStore = window.down('gosModuleCoreEventTriggerGrid').getStore();
        triggerStore.getProxy().setExtraParam('eventId', record.get('id'));
        triggerStore.load();
    },
    deleteFunction: function(records) {
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