Ext.define('GibsonOS.module.core.module.TabPanel', {
    extend: 'GibsonOS.module.core.component.tab.Panel',
    alias: ['widget.gosModuleCoreModuleTabPanel'],
    itemId: 'coreModuleTabPanel',
    initComponent() {
        const me = this;

        me.items = [{
            xtype: 'gosModuleCoreModulePermissionPanel',
            title: 'Rechte'
        },{
            xtype: 'gosModuleCoreModuleSettingGrid',
            title: 'Einstellungen'
        }];

        me.callParent();

        me.down('gosModuleCoreModulePermissionUserGrid').getStore().on('load', (store) => {
            const data = store.getProxy().getReader().rawData;
            let requiredPermissions = null;
            
            if (data.requiredPermissions) {
                requiredPermissions = data.requiredPermissions;
            }

            const panel = me.down('#coreModulePermissionPanel').down('panel');

            if (requiredPermissions) {
                panel.show();
            } else {
                panel.hide();
            }

            panel.update(requiredPermissions);
        });

        me.down('gosModuleCoreModulePermissionUserGrid').getStore().on('update', (store) => {
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