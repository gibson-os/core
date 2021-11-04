Ext.define('GibsonOS.module.core.module.permission.Panel', {
    extend: 'GibsonOS.Panel',
    alias: ['widget.gosModuleCoreModulePermissionPanel'],
    itemId: 'coreModulePermissionPanel',
    layout: 'border',

    initComponent: function() {
        var me = this;

        me.items = [{
            region: 'north',
            flex: 0,
            hidden: true,
            frame: true,
            data: [],
            tpl: new Ext.XTemplate(
                '<table class="gibson_status_table">',
                '<tpl for=".">',
                    '<tr>',
                        '<th>Verwendetes Recht:</th>',
                        '<td>{[this.test(values)]}</td>',
                    '</tr>',
                '</tpl>',
                '</table>',
                {
                    test: function(permission) {
                        var permissionString = null;

                        Ext.each(GibsonOS.module.core.module.data.permissions, function(item) {
                            if (permission == item[0]) {
                                permissionString = item[1];
                                return false;
                            }
                        });

                        return permissionString;
                    }
                }
            )
        },{
            xtype: 'gosModuleCoreModulePermissionGrid',
            region: 'center'
        }];

        me.callParent();
    }
});