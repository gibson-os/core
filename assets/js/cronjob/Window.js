Ext.define('GibsonOS.module.core.cronjob.Window', {
    extend: 'GibsonOS.Window',
    alias: ['widget.gosModuleCoreCronjobWindow'],
    title: 'Cronjob',
    width: 600,
    height: 400,
    maximizable: true,
    initComponent: function() {
        let me = this;

        me.items = [{
            xtype: 'gosModuleCoreCronjobPanel'
        }];

        me.callParent();
    }
});