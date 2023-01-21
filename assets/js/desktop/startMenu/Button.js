Ext.define('GibsonOS.module.core.desktop.startMenu.Button', {
    extend: 'Ext.menu.Item',
    alias: ['widget.gosCoreDesktopStartMenuButton'],
    cls: 'startmenu_button',
    anchor: '100%',
    getSourceElement(event) {
        return event.getTarget('.x-menu-item-link');
    },
    initComponent() {
        let me = this;

        me = GibsonOS.decorator.Drag.init(me);

        me.callParent();

        GibsonOS.decorator.Drag.addListeners(me);
    }
});