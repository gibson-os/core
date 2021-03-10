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
    initComponent: function() {
        let me = this;

        me = GibsonOS.decorator.Panel.init(me);

        if (typeof(me.getColumns) === 'function') {
            me.columns = me.getColumns();
        }

        me.callParent();

        GibsonOS.decorator.Panel.addListeners(me);

        /*if (me.down('gosToolbarPaging')) {
            me.getStore().on('add', function (store, records) {
                store.totalCount += records.length;
                grid.down('gosToolbarPaging').onLoad();
            }, me, {
                priority: 999
            });

            me.getStore().on('remove', function (store) {
                store.totalCount--;
                grid.down('gosToolbarPaging').onLoad();
            }, me, {
                priority: 999
            });
        }*/
    }
});