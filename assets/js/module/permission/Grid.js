Ext.define('GibsonOS.module.core.module.permission.Grid', {
    extend: 'GibsonOS.module.core.component.grid.Panel',
    alias: ['widget.gosModuleCoreModulePermissionGrid'],
    itemId: 'coreModulePermissionGrid',
    plugins: [{
        ptype: 'gosGridPluginCellEditing'
    }],
    initComponent() {
        const me = this;

        me.store = new GibsonOS.module.core.module.store.Permission();

        me.callParent();

        me.store.on('update', (store, record) => {
            GibsonOS.Ajax.request({
                url: baseDir + 'core/user/savePermission',
                params: {
                    id: record.get('userId'),
                    permission: record.get('permission'),
                    module: record.get('moduleName'),
                    task: record.get('taskName'),
                    action: record.get('actionName')
                },
                failure: function() {
                    GibsonOS.MessageBox.show({msg: 'Berechtigung konnte nicht gespeichert werden!'});
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
            header: 'Host',
            dataIndex: 'userHost',
            flex: 1
        },{
            header: 'IP',
            dataIndex: 'userIp',
            flex: 1
        },{
            header: 'Rechte',
            dataIndex: 'permission',
            width: 300,
            editor: {
                xtype: 'gosFormComboBox',
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