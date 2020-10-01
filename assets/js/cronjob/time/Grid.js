Ext.define('GibsonOS.module.core.cronjob.time.Grid', {
    extend: 'GibsonOS.grid.Panel',
    alias: ['widget.gosModuleCoreCronjobTimeGrid'],
    itemId: 'coreCronjobGrid',
    initComponent: function() {
        var me = this;

        me.store = new GibsonOS.module.core.cronjob.store.Time({
            gos: me.gos
        });
        me.columns = [{
            header: 'Stunden',
            dataIndex: 'hour',
            flex: 1,
            sortable: false
        },{
            header: 'Minuten',
            dataIndex: 'minute',
            flex: 1,
            sortable: false
        },{
            header: 'Sekunden',
            dataIndex: 'second',
            flex: 1,
            sortable: false
        },{
            header: 'Monatstage',
            dataIndex: 'day_of_month',
            flex: 1,
            sortable: false
        },{
            header: 'Wochentage',
            dataIndex: 'day_of_week',
            flex: 1,
            sortable: false
        },{
            header: 'Monate',
            dataIndex: 'month',
            flex: 1,
            sortable: false
        },{
            header: 'Jahre',
            dataIndex: 'year',
            flex: 1,
            sortable: false
        }];
        me.tbar = [{
            iconCls: 'icon_system system_add',
            handler: function() {

            }
        }];

        me.callParent();
    }
});