Ext.define('GibsonOS.module.core.component.tab.Panel', {
    extend: 'Ext.tab.Panel',
    alias: ['widget.gosCoreComponentTabPanel'],
    border: false,
    frame: false,
    plain: true,
    flex: 1,
    defaults: {
        xtype: 'gosCoreComponentPanel'
    },
    enableToolbar: true,
    enableKeyEvents: true,
    enableClickEvents: false,
    enableContextMenu: false,
    initComponent: function() {
        let me = this;

        me = GibsonOS.decorator.Panel.init(me);

        me.callParent();

        GibsonOS.decorator.Panel.addListeners(me);
    }
});