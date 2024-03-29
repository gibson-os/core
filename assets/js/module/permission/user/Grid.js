Ext.define('GibsonOS.module.core.module.permission.user.Grid', {
    extend: 'GibsonOS.module.core.component.grid.Panel',
    alias: ['widget.gosModuleCoreModulePermissionUserGrid'],
    plugins: [{
        ptype: 'gosGridPluginCellEditing'
    }],
    initComponent() {
        const me = this;

        me.store = new GibsonOS.module.core.module.store.user.Permission();

        me.callParent();

        me.store.on('update', (store, record) => {
            GibsonOS.Ajax.request({
                url: baseDir + 'core/user/permission',
                method: 'POST',
                params: {
                    id: record.get('id'),
                    userId: record.get('userId'),
                    permission: record.get('permission'),
                    moduleId: record.get('moduleId'),
                    taskId: record.get('taskId'),
                    actionId: record.get('actionId')
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
            header: 'Benutzer',
            dataIndex: 'userName',
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