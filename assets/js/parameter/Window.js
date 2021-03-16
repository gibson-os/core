Ext.define('GibsonOS.module.core.parameter.Window', {
    extend: 'GibsonOS.Window',
    alias: ['widget.gosModuleCoreParameterWindow'],
    title: 'Parameter',
    width: 420,
    y: 50,
    autoHeight: true,
    maximizable: true,
    withOperator: false,
    withSet: false,
    requiredPermission: {
        module: 'core',
        task: 'event'
    },
    initComponent: function() {
        let me = this;

        me.items = [{
            xtype: 'gosModuleCoreParameterForm',
            withOperator: me.withOperator
        }];

        me.callParent();

        me.down('#coreEventElementParameterSaveButton').on('click', function() {
            me.close();
        }, this, {
            priority: -999
        });
    }
});