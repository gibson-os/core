Ext.define('GibsonOS.module.core.event.element.parameter.Window', {
    extend: 'GibsonOS.Window',
    alias: ['widget.gosModuleCoreEventElementParameterWindow'],
    title: 'Parameter',
    width: 400,
    autoHeight: true,
    maximizable: true,
    requiredPermission: {
        module: 'core',
        task: 'event'
    },
    initComponent: function() {
        let me = this;

        me.items = [{
            xtype: 'gosModuleCoreEventElementParameterForm',
            gos: me.gos
        }];

        me.callParent();

        me.down('#coreEventElementParameterSaveButton').on('click', function() {
            me.close();
        }, this, {
            priority: -999
        });
    }
});