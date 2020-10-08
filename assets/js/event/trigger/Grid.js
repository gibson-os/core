Ext.define('GibsonOS.module.core.event.trigger.Grid', {
    extend: 'GibsonOS.core.component.grid.Panel',
    alias: ['widget.gosModuleCoreEventTriggerGrid'],
    autoScroll: true,
    initComponent: function() {
        let me = this;

        me.store = new GibsonOS.module.core.event.trigger.store.Grid();
        me.store.getProxy().setExtraParam('eventId', me.gos.data.eventId);
        me.store.load();

        me.plugins = [
            Ext.create('Ext.grid.plugin.RowEditing', {
                saveBtnText: 'Speichern',
                cancelBtnText: 'Abbrechen',
                clicksToMoveEditor: 1,
                pluginId: 'rowEditing'
            })
        ];

        me.callParent();
    },
    addFunction: function() {
        let me = this;
        me.plugins[0].startEdit(me.getStore().add({})[0], 1);
    },
    deleteFunction: function() {
        let me = this;
        me.getStore().remove(me.getSelectionModel().getSelection());
    },
    getColumns: function() {
        return [{
            header: 'Trigger',
            dataIndex: 'trigger',
            flex: 1,
            editor: {
                xtype: 'gosFormTextfield',
                hideLabel: true
            }
        },{
            header: 'Wochentag',
            dataIndex: 'weekday',
            width: 70,
            editor: {
                xtype: 'gosFormComboBox',
                emptyText: 'Keiner',
                hideLabel: true,
                store: {
                    xtype: 'gosDataStore',
                    fields: ['id', 'name'],
                    data: [{
                        id: null,
                        name: 'Keiner'
                    },{
                        id: 1,
                        name: 'Montag'
                    },{
                        id: 2,
                        name: 'Dienstag'
                    },{
                        id: 3,
                        name: 'Mittwoch'
                    },{
                        id: 4,
                        name: 'Donnerstag'
                    },{
                        id: 5,
                        name: 'Freitag'
                    },{
                        id: 6,
                        name: 'Samstag'
                    },{
                        id: 0,
                        name: 'Sonntag'
                    },{
                        id: 1,
                        name: 'Montag'
                    }]
                }
            }
        },{
            header: 'Tag',
            dataIndex: 'day',
            width: 50,
            editor: {
                xtype: 'gosFormNumberfield',
                minValue: 1,
                maxValue: 31,
                hideLabel: true
            }
        },{
            header: 'Monat',
            dataIndex: 'month',
            width: 50,
            editor: {
                xtype: 'gosFormNumberfield',
                minValue: 1,
                maxValue: 12,
                hideLabel: true
            }
        },{
            header: 'Jahr',
            dataIndex: 'year',
            width: 50,
            editor: {
                xtype: 'gosFormNumberfield',
                minValue: 0,
                maxValue: 9999,
                hideLabel: true
            }
        },{
            header: 'Stunde',
            dataIndex: 'hour',
            width: 50,
            editor: {
                xtype: 'gosFormNumberfield',
                minValue: 0,
                maxValue: 23,
                hideLabel: true
            }
        },{
            header: 'Minute',
            dataIndex: 'minute',
            width: 50,
            editor: {
                xtype: 'gosFormNumberfield',
                minValue: 0,
                maxValue: 59,
                hideLabel: true
            }
        },{
            header: 'Sekunde',
            dataIndex: 'second',
            width: 55,
            editor: {
                xtype: 'gosFormNumberfield',
                minValue: 0,
                maxValue: 59,
                hideLabel: true
            }
        }];
    }
});