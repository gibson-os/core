Ext.define('GibsonOS.core.component.grid.Panel', {
    extend: 'Ext.grid.Panel',
    alias: ['widget.gosCoreComponentGridPanel'],
    border: false,
    flex: 1,
    enablePagingBar: true,
    getColumns: null,
    enableToolbar: true,
    enableKeyEvents: true,
    enableContextMenu: true,
    initComponent: function() {
        let me = this;

        me = GibsonOS.decorator.ActionManager.init(me);
        me = GibsonOS.decorator.AutoReload.init(me);
        me = GibsonOS.decorator.action.Add.init(me);
        me = GibsonOS.decorator.action.Delete.init(me);

        if (typeof(me.getColumns) === 'function') {
            me.columns = me.getColumns();
        }

        me.callParent();

        GibsonOS.decorator.ActionManager.addListeners(me);
        GibsonOS.decorator.AutoReload.addListeners(me);

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