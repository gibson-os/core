Ext.define('GibsonOS.core.component.tree.Panel', {
    extend: 'Ext.tree.Panel',
    alias: ['widget.gosCoreComponentTreePanel'],
    border: false,
    frame: false,
    plain: true,
    flex: 1,
    rootVisible: false,
    enablePagingBar: true,
    enableToolbar: true,
    enableKeyEvents: true,
    enableClickEvents: true,
    enableContextMenu: true,
    initComponent: function() {
        let me = this;

        me = GibsonOS.decorator.Panel.init(me);

        if (typeof(me.getColumns) === 'function') {
            me.columns = me.getColumns();
        }

        me.callParent();

        GibsonOS.decorator.Panel.addListeners(me);
    }
});