Ext.define('GibsonOS.module.core.module.permission.Grid', {
    extend: 'GibsonOS.grid.Panel',
    alias: ['widget.gosModuleCoreModulePermissionGrid'],
    itemId: 'coreModulePermissionGrid',
    plugins: [{
        ptype: 'gosGridPluginCellEditing'
    }],
    initComponent: function() {
        this.store = new GibsonOS.module.core.module.store.Permission();
        this.columns = [{
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
            renderer: function(value, meta, record) {
                var newValue = null;

                Ext.each(GibsonOS.module.core.module.data.permissions, function(item) {
                    if (value != 0) {
                        if (value == item[0]) {
                            newValue = item[1];
                            return false;
                        }
                    } else if (record.get('parentPermission') == item[0]) {
                        newValue = '- Geerbt (' + item[1] + ') -';
                        return false;
                    }
                });

                return newValue;
            }
        }];

        this.callParent();
    }
});