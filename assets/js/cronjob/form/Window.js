Ext.define('GibsonOS.module.core.cronjob.form.Window', {
    extend: 'GibsonOS.Window',
    alias: ['widget.gosModuleCoreCronjobFormWindow'],
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