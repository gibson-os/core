Ext.define('GibsonOS.module.core.role.App', {
    extend: 'GibsonOS.App',
    alias: ['widget.gosModuleCoreRoleApp'],
    id: 'coreRole',
    appIcon: 'icon_user',
    title: 'Rollen',
    width: 400,
    height: 260,
    requiredPermission: {
        module: 'core',
        task: 'role'
    },
    initComponent() {
        let me = this;

        me = GibsonOS.decorator.Panel.init(me);

        me.items = [{
            layout: 'border',
            items: [{
                xtype: 'gosModuleCoreRoleGrid',
                region: 'west',
                width: 200,
                flex: 0,
                hideCollapseTool: true
            },{
                xtype: 'gosModuleCoreRoleUserGrid',
                region: 'center',
                layout: 'fit'
            }]
        }];

        me.callParent();

        me.viewItem = me.down('gosModuleCoreRoleGrid');
        GibsonOS.decorator.Panel.addListeners(me);

        me.viewItem.on('selectionchange', (grid, records) => {
            const userGrid = me.down('gosModuleCoreRoleUserGrid');

            if (records.length !== 1 || !records[0].getId()) {
                userGrid.getStore().removeAll();

                return;
            }

            userGrid.getStore().getProxy().setExtraParam('id', records[0].getId());
            userGrid.getStore().load();
        });
    }
});