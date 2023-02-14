Ext.define('GibsonOS.module.core.module.permission.role.Grid', {
    extend: 'GibsonOS.module.core.component.grid.Panel',
    alias: ['widget.gosModuleCoreModulePermissionRoleGrid'],
    plugins: [{
        ptype: 'gosGridPluginCellEditing'
    }],
    initComponent() {
        const me = this;

        me.store = new GibsonOS.module.core.module.store.role.Permission();

        me.callParent();

        me.store.on('update', (store, record) => {
            GibsonOS.Ajax.request({
                url: baseDir + 'core/role/savePermission',
                params: {
                    id: record.get('id'),
                    roleId: record.get('roleId'),
                    permission: record.get('permission'),
                    module: record.get('moduleName'),
                    task: record.get('taskName'),
                    action: record.get('actionName')
                },
                success() {
                    me.store.load();
                }
            });
        }, me.store, {
            priority: -999
        });
    },
    getColumns() {
        return [{
            header: 'Rolle',
            dataIndex: 'roleName',
            flex: 1
        },{
            header: 'Berechtigung',
            dataIndex: 'permission',
            width: 300,
            editor: {
                xtype: 'gosCoreComponentFormFieldComboBox',
                id: 'coreModuleManagePermissionCombo',
                typeAhead: true,
                triggerAction: 'all',
                selectOnTab: true,
                store: GibsonOS.module.core.module.data.permissions,
                lazyRender: true,
                listClass: 'x-combo-list-small'
            },
            renderer(value, meta, record) {
                let newValue = null;

                Ext.each(GibsonOS.module.core.module.data.permissions, (item) => {
                    if (value !== 0) {
                        if (value === item[0]) {
                            newValue = item[1];
                            return false;
                        }
                    } else if (record.get('parentPermission') === item[0]) {
                        newValue = '- Geerbt (' + item[1] + ') -';
                        return false;
                    }
                });

                return newValue;
            }
        }];
    }
});