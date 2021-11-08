Ext.define('GibsonOS.module.core.icon.App', {
    extend: 'GibsonOS.App',
    alias: ['widget.gosModuleCoreIconApp'],
    id: 'coreIconManage',
    title: 'Icons',
    width: 600,
    height: 260,
    requiredPermission: {
        module: 'core',
        task: 'icon'
    },
    initComponent: function() {
        this.items = [{
            xtype: 'gosModuleCoreIconPanel'
        }];

        this.callParent();
    }
});