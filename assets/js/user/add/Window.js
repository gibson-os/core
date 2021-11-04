Ext.define('GibsonOS.module.core.user.add.Window', {
    extend: 'GibsonOS.Window',
    alias: ['widget.gosModuleCoreUserAddWindow'],
    itemId: 'coreUserAddWindow',
    title: 'Benutzer hinzufügen',
    width: 400,
    height: 210,
    initComponent: function() {
        var window = this;

        this.items = [{
            xtype: 'gosModuleCoreUserForm',
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