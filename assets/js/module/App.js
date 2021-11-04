Ext.define('GibsonOS.module.core.module.App', {
    extend: 'GibsonOS.App',
    alias: ['widget.gosModuleCoreModuleApp'],
    id: 'coreModuleManage',
    title: 'Module',
    appIcon: 'icon_modules',
    width: 700,
    height: 300,
    initComponent: function() {
        var app = this;

        this.items = [{
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
                    itemclick: function(tree, record, item, index, event, options) {
                        var id = record.get('id');
                        var settingStore = app.down('#coreModuleManageSettingsGrid').getStore();
                        var permissionStore = app.down('#coreModulePermissionGrid').getStore();
                        var permissionPanel = app.down('#coreModulePermissionPanel');

                        if (!isNaN(id)) {
                            settingStore.getProxy().extraParams.module = id;
                            settingStore.load();
                        }

                        permissionStore.getProxy().extraParams.node = id;
                        permissionStore.load();
                    }
                }
            },{
                xtype: 'gosModuleCoreModuleTabPanel',
                region: 'center'
            }]
        }];
        this.tbar = [{
            text: 'Scan',
            handler: function() {
                var button = this;
                button.disable();

                GibsonOS.module.core.module.fn.scan({
                    success: function(response, options) {
                        app.down('#coreModuleTree').getStore().load();
                        app.down('#coreModulePermissionGrid').getStore().loadData([]);
                        app.down('#coreModuleManageSettingsGrid').getStore().loadData([]);
                        button.enable();
                    },
                    failure: function(response, options) {
                        button.enable();
                    }
                });
            }
        }];

        this.callParent();
    }
});