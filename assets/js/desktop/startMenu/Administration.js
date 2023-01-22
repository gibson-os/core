Ext.define('GibsonOS.module.core.desktop.startMenu.Administration', {
    extend: 'GibsonOS.module.core.desktop.startMenu.Button',
    alias: ['widget.gosCoreDesktopStartMenuAdministration'],
    text: 'Verwaltung',
    cls: 'startmenu_button',
    menu: [{
        xtype: 'gosCoreDesktopStartMenuButton',
        text: 'Benutzer',
        iconCls: 'icon16 icon_user',
        handler() {
            new GibsonOS.module.core.user.App();
        }
    },{
        xtype: 'gosCoreDesktopStartMenuButton',
        text: 'Module',
        iconCls: 'icon16 icon_modules',
        handler() {
            new GibsonOS.module.core.module.App();
        }
    },{
        xtype: 'gosCoreDesktopStartMenuButton',
        text: 'Icons',
        handler() {
            new GibsonOS.module.core.icon.App();
        }
    },{
        xtype: 'gosCoreDesktopStartMenuButton',
        text: 'Cronjobs',
        handler() {
            new GibsonOS.module.core.cronjob.App();
        }
    },{
        xtype: 'gosCoreDesktopStartMenuButton',
        text: 'Events',
        handler() {
            new GibsonOS.module.core.event.App();
        }
    }]
});