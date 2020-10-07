Ext.define('GibsonOS.module.core.event.trigger.Grid', {
    extend: 'GibsonOS.grid.Panel',
    alias: ['widget.gosModuleCoreEventTriggerGrid'],
    autoScroll: true,
    initComponent: function () {
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
        me.columns = [{
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
            width: 70
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

        me.tbar = [{
            xtype: 'gosButton',
            iconCls: 'icon_system system_add',
            requiredPermission: {
                action: 'save',
                permission: GibsonOS.Permission.WRITE
            },
            handler: function () {
                me.plugins[0].startEdit(me.getStore().add({})[0], 1);
            }
        },{
            xtype: 'gosButton',
            iconCls: 'icon_system system_delete',
            disabled: true,
            requiredPermission: {
                action: 'save',
                permission: GibsonOS.Permission.WRITE
            },
            handler: function() {
            }
        }];

        me.callParent();
    }
});