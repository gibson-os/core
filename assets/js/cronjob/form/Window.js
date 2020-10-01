Ext.define('GibsonOS.module.core.cronjob.form.Window', {
    extend: 'GibsonOS.Window',
    alias: ['widget.gosModuleCoreCronjobFormWindow'],
    itemId: 'coreCronjobFormWindow',
    title: 'Cronjob',
    width: 600,
    height: 400,
    layout: 'border',
    initComponent: function() {
        let me = this;

        me.items = [{
            xtype: 'gosModuleCoreCronjobForm',
            region: 'north',
            flex: 0,
            autoHeight: true,
            gos: me.gos
        },{
            xtype: 'gosModuleCoreCronjobTimeGrid',
            region: 'center',
            gos: me.gos
        }];

        me.callParent();
    }
});