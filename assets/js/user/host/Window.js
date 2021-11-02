Ext.define('GibsonOS.module.system.user.host.Window', {
    extend: 'GibsonOS.Window',
    alias: ['widget.gosModuleSystemUserHostWindow'],
    itemId: 'systemUserHostWindow',
    title: 'Automatischer Login hinzufügen',
    width: 300,
    height: 120,
    initComponent: function() {
        this.items = [{
            xtype: 'gosModuleSystemUserHostForm',
            gos: {
                data: this.gos.data
            }
        }];

        this.callParent();
    }
});