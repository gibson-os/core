Ext.define('GibsonOS.module.core.module.TabPanel', {
    extend: 'GibsonOS.TabPanel',
    alias: ['widget.gosModuleCoreModuleTabPanel'],
    itemId: 'coreModuleTabPanel',
    initComponent: function() {
        var me = this;

        me.items = [{
            xtype: 'gosModuleCoreModulePermissionPanel',
            title: 'Rechte'
        },{
            xtype: 'gosModuleCoreModuleSettingGrid',
            title: 'Einstellungen'
        }];

        me.callParent();

        me.down('#coreModulePermissionGrid').getStore().on('load', function(store) {
            var data = store.getProxy().getReader().rawData;
            var requiredPermissions = null;
            
            if (data.requiredPermissions) {
                requiredPermissions = data.requiredPermissions;
            }

            var panel = me.down('#coreModulePermissionPanel').down('panel');

            if (requiredPermissions) {
                panel.show();
            } else {
                panel.hide();
            }

            panel.update(requiredPermissions);
        });

        me.down('#coreModulePermissionGrid').getStore().on('update', function(store, record, operation, options) {
            var nodes = [];
            var app = me.up('#app');

            var getNodes = function(node) {
                if (node.parentNode) {
                    nodes.unshift(node.get('text'));
                    getNodes(node.parentNode);
                }
            };

            getNodes(app.down('#coreModuleTree').getSelectionModel().getSelection()[0]);

            if (!store.gos) {
                store.gos = {};
            }

            store.gos.data = {
                module: nodes[0],
                task: nodes.length > 1 ? nodes[1] : null,
                action: nodes.length > 2 ? nodes[2] : null
            };
        });
    }
});