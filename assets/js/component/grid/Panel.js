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

        if (typeof(me.getColumns) === 'function') {
            me.columns = me.getColumns();
        }

        if (me.store !== undefined) {
            me.store.on('load', (store) => {
                const jsonData = store.getProxy().getReader().jsonData;

                if (typeof(jsonData.filters) === 'object' && !Ext.Object.isEmpty(jsonData.filters)) {
                    me.setFilters(jsonData.filters);
                }

                Ext.iterate(me.columns, (column) => {
                    column.sortable = (jsonData.possibleOrders ?? []).indexOf(column.dataIndex) !== -1;
                });
            });
        }

        me.callParent();

        GibsonOS.decorator.Panel.addListeners(me);
    },
    filterFunction(filters) {
        const me = this;

        console.log(filters);
        me.store.getProxy().setExtraParam('filters', Ext.encode(filters));
        me.store.load();
    }
});