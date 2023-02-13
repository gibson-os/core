Ext.define('GibsonOS.module.core.module.App', {
    extend: 'GibsonOS.App',
    alias: ['widget.gosModuleCoreModuleApp'],
    id: 'coreModuleManage',
    title: 'Module',
    appIcon: 'icon_modules',
    width: 700,
    height: 400,
    initComponent() {
        const me = this;

        me.items = [{
            layout: 'border',
            items: [{
                xtype: 'gosModuleCoreModuleTree',
                region: 'west',
                flex: 0,
                collapsible: true,
                split: true,
                width: 150,
                hideCollapseTool: true,
                listeners: {
                    itemclick(tree, record) {
                        const id = record.get('id');
                        const settingStore = me.down('#coreModuleManageSettingsGrid').getStore();
                        const userPermissionStore = me.down('gosModuleCoreModulePermissionUserGrid').getStore();
                        const rolePermissionStore = me.down('gosModuleCoreModulePermissionRoleGrid').getStore();

                        if (!isNaN(id)) {
                            settingStore.getProxy().extraParams.moduleId = id;
                            settingStore.load();
                        }

                        userPermissionStore.getProxy().extraParams.node = id;
                        userPermissionStore.load();

                        rolePermissionStore.getProxy().extraParams.node = id;
                        rolePermissionStore.load();
                    }
                }
            },{
                xtype: 'gosModuleCoreModuleTabPanel',
                region: 'center'
            }]
        }];
        me.tbar = [{
            text: 'Scan',
            handler: function() {
                const button = this;
                button.disable();

                GibsonOS.module.core.module.fn.scan({
                    success() {
                        me.down('#coreModuleTree').getStore().load();
                        me.down('gosModuleCoreModulePermissionUserGrid').getStore().loadData([]);
                        me.down('gosModuleCoreModulePermissionRoleGrid').getStore().loadData([]);
                        me.down('#coreModuleManageSettingsGrid').getStore().loadData([]);
                        button.enable();
                    },
                    failure() {
                        button.enable();
                    }
                });
            }
        }];

        me.callParent();
    }
});