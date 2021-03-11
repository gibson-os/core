Ext.define('GibsonOS.module.core.event.Grid', {
    extend: 'GibsonOS.module.core.component.grid.Panel',
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
        const me = this;

        Ext.MessageBox.confirm(
            'Wirklich löschen?',
            'Möchtest du das Event ' + records[0].get('name') + ' wirklich löschen?', buttonId => {
                if (buttonId === 'no') {
                    return false;
                }

                me.setLoading(true);

                GibsonOS.Ajax.request({
                    url: baseDir + 'core/event/delete',
                    params: {
                        eventId: records[0].get('id')
                    },
                    success() {
                        me.getStore().load();
                    },
                    callback() {
                        me.setLoading(false);
                    }
                });
            }
        );
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
            header: 'Letzter Lauf',
            dataIndex: 'lastRun',
            width: 120
        },{
            xtype: 'booleancolumn',
            header: 'Aktiv',
            dataIndex: 'active',
            trueText: 'Ja',
            falseText: 'Nein',
            width: 50
        },{
            xtype: 'booleancolumn',
            header: 'Asynchron',
            dataIndex: 'async',
            trueText: 'Ja',
            falseText: 'Nein',
            width: 70
        }];
    }
});