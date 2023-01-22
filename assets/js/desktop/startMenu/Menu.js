Ext.define('GibsonOS.module.core.desktop.startMenu.Menu', {
    extend: 'GibsonOS.Button',
    alias: ['widget.gosCoreDesktopStartMenuMenu'],
    iconCls: 'icon16 icon_logo',
    anchor: '100%',
    store: null,
    initComponent() {
        const me = this;

        me.menu = [{
            xtype: 'gosCoreDesktopStartMenuApps',
            store: me.store
        },{
            xtype: 'gosCoreDesktopStartMenuAdministration',
        },{
            xtype: 'gosCoreDesktopStartMenuButton',
            text: 'Einstellungen',
            iconCls: 'icon16 icon_settings',
            handler() {
                new GibsonOS.module.core.user.setting.App();
            }
        },('-'),{
            xtype: 'gosCoreDesktopStartMenuButton',
            text: 'Logout',
            iconCls: 'icon_system system_exit',
            handler() {
                document.location = baseDir + 'core/user/logout';
            }
        }];

        me.callParent();
    }
});