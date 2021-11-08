Ext.define('GibsonOS.module.core.icon.form.Window', {
    extend: 'GibsonOS.Window',
    alias: ['widget.gosModuleCoreIconFormWindow'],
    itemId: 'coreIconFormWindow',
    title: 'Icon hinzufügen',
    width: 300,
    height: 170,
    requiredPermission: {
        module: 'core',
        task: 'icon'
    },
    initComponent: function() {
        this.items = [{
            xtype: 'gosModuleCoreIconFormPanel',
            gos: {
                data: this.gos.data
            }
        }];

        this.callParent();
    }
});