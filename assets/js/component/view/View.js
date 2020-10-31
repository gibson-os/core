Ext.define('GibsonOS.core.component.view.View', {
    extend: 'Ext.view.View',
    alias: ['widget.gosCoreComponentViewView'],
    border: false,
    flex: 1,
    frame: false,
    plain: true,
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