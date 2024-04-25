Ext.define('GibsonOS.module.core.event.Window', {
    extend: 'GibsonOS.Window',
    alias: ['widget.gosModuleCoreEventWindow'],
    title: 'Event',
    width: 800,
    height: 500,
    maximizable: true,
    requiredPermission: {
        module: 'core',
        task: 'event'
    },
    initComponent: function() {
        let me = this;

        me.items = [{
            xtype: 'gosModuleCoreEventPanel'
        }];

        me.callParent();
    }
});