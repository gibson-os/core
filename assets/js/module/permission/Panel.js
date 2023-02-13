Ext.define('GibsonOS.module.core.module.permission.Panel', {
    extend: 'GibsonOS.Panel',
    alias: ['widget.gosModuleCoreModulePermissionPanel'],
    itemId: 'coreModulePermissionPanel',
    layout: 'border',
    initComponent() {
        const me = this;

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
                        '<th>Benötigte Berechtigung:</th>',
                        '<td>{[this.text(values)]}</td>',
                    '</tr>',
                '</tpl>',
                '</table>',
                {
                    text(permission) {
                        let permissionString = null;

                        Ext.each(GibsonOS.module.core.module.data.permissions, (item) => {
                            if (permission === item[0]) {
                                permissionString = item[1];

                                return false;
                            }
                        });

                        return permissionString;
                    }
                }
            )
        },{
            xtype: 'gosCoreComponentTabPanel',
            region: 'center',
            items: [{
                xtype: 'gosModuleCoreModulePermissionUserGrid',
                title: 'Benutzer'
            },{
                xtype: 'gosModuleCoreModulePermissionRoleGrid',
                title: 'Rollen'
            }]
        }];

        me.callParent();
    }
});