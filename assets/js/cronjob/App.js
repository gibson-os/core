Ext.define('GibsonOS.module.core.cronjob.App', {
    extend: 'GibsonOS.App',
    alias: ['widget.gosModuleCoreCronjobApp'],
    id: 'coreCronjob',
    title: 'Cronjobs',
    width: 600,
    height: 260,
    requiredPermission: {
        module: 'core',
        task: 'cronjob'
    },
    initComponent: function() {
        let me = this;

        me.items = [{
            xtype: 'gosModuleCoreCronjobGrid'
        }];

        me.callParent();
    }
});