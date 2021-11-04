Ext.define('GibsonOS.module.core.user.setting.App', {
    extend: 'GibsonOS.App',
    alias: ['widget.gosModuleCoreUserSettingApp'],
    itemId: 'coreUserSettingApp',
    appIcon: 'icon_settings',
    title: 'Einstellungen',
    width: 400,
    height: 260,
    requiredPermission: {
        module: 'core',
        task: 'user'
    },
    initComponent: function() {
        var app = this;

        this.items = [{
            xtype: 'gosModuleCoreUserTabPanel',
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