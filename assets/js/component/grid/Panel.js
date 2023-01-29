Ext.define('GibsonOS.module.core.component.grid.Panel', {
    extend: 'Ext.grid.Panel',
    alias: ['widget.gosCoreComponentGridPanel'],
    border: false,
    flex: 1,
    enablePagingBar: true,
    enableToolbar: true,
    enableKeyEvents: true,
    enableClickEvents: true,
    enableContextMenu: true,
    initComponent() {
        let me = this;

        me = GibsonOS.decorator.Panel.init(me);
        me = GibsonOS.decorator.PagingBar.init(me);

        if (typeof(me.getColumns) === 'function') {
            me.columns = me.getColumns();
        }

        me.callParent();

        GibsonOS.decorator.Panel.addListeners(me);
    }
});