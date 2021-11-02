Ext.define('GibsonOS.module.system.user.add.Window', {
    extend: 'GibsonOS.Window',
    alias: ['widget.gosModuleSystemUserAddWindow'],
    itemId: 'systemUserAddWindow',
    title: 'Benutzer hinzufügen',
    width: 400,
    height: 210,
    initComponent: function() {
        var window = this;

        this.items = [{
            xtype: 'gosModuleSystemUserForm',
            gos: {
                data: {
                    add: true,
                    success: function(formAction, action) {
                        if (window.gos.data.success) {
                            window.gos.data.success(formAction, action);
                        }
                    }
                }
            }
        }];

        this.callParent();
    }
});