Ext.define('GibsonOS.module.system.user.setting.App', {
    extend: 'GibsonOS.App',
    alias: ['widget.gosModuleSystemUserSettingApp'],
    itemId: 'systemUserSettingApp',
    appIcon: 'icon_settings',
    title: 'Einstellungen',
    width: 400,
    height: 260,
    requiredPermission: {
        module: 'system',
        task: 'user'
    },
    initComponent: function() {
        var app = this;

        this.items = [{
            xtype: 'gosModuleSystemUserTabPanel',
            gos: {
                data: {
                    success: function(form, action) {
                        app.close();
                    }
                }
            }
        }];

        this.callParent();
    }
});