Ext.define('GibsonOS.module.core.event.App', {
    extend: 'GibsonOS.App',
    alias: ['widget.gosModuleCoreEventApp'],
    title: 'Events',
    width: 600,
    height: 400,
    requiredPermission: {
        module: 'core',
        task: 'event'
    },
    initComponent: function() {
        let me = this;

        me.items = [{
            xtype: 'gosModuleCoreEventGrid'
        }];

        me.callParent();
    }
});