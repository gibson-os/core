Ext.define('GibsonOS.core.component.grid.Panel', {
    extend: 'Ext.grid.Panel',
    alias: ['widget.gosCoreComponentGridPanel'],
    border: false,
    flex: 1,
    enablePagingBar: true,
    enableToolbar: true,
    enableKeyEvents: true,
    getColumns: null,
    initComponent: function() {
        let me = this;

        me = GibsonOS.decorator.autoReload.init(me);
        me = GibsonOS.decorator.action.add.init(me);
        me = GibsonOS.decorator.action.delete.init(me);

        if (typeof(me.getColumns) === 'function') {
            me.columns = me.getColumns();
        }

        me.callParent();

        if (me.itemContextMenu) {
            me.itemContextMenu = new GibsonOS.contextMenu.ContextMenu({
                items: me.itemContextMenu,
                parent: me
            });
        }

        me.on('itemcontextmenu', function(grid, record, item, index, event, options) {
            if (me.itemContextMenu) {
                me.itemContextMenu.record = record;
                event.stopEvent();
                me.itemContextMenu.showAt(event.getXY());
            }
        });

        if (me.containerContextMenu) {
            me.containerContextMenu = new GibsonOS.contextMenu.ContextMenu({
                items: me.containerContextMenu,
                parent: me
            });
        }

        me.on('containercontextmenu', function(grid, event, options) {
            if (me.containerContextMenu) {
                event.stopEvent();
                me.containerContextMenu.showAt(event.getXY());
            }
        });

        GibsonOS.decorator.autoReload.addListeners(me);
        GibsonOS.decorator.action.delete.addListeners(me);

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