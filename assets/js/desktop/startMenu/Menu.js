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
        }];

        me.callParent();
    }
});