Ext.define('GibsonOS.module.core.component.tree.Panel', {
    extend: 'Ext.tree.Panel',
    alias: ['widget.gosCoreComponentTreePanel'],
    border: false,
    frame: false,
    plain: true,
    flex: 1,
    rootVisible: false,
    enablePagingBar: false,
    enableToolbar: true,
    enableKeyEvents: true,
    enableClickEvents: true,
    enableContextMenu: true,
    initComponent: function() {
        let me = this;

        me = Ext.merge(me, Ext.merge({
            insertRecords(beforeRecord, records) {
                const parentNode = beforeRecord.parentNode;
                let index = parentNode.indexOf(beforeRecord);

                Ext.iterate(records, (record) => {
                    parentNode.insertChild(index++, record);
                });
            },
            addRecords(records) {
                let me = this;

                me.getStore().getRootNode().appendChild(records);
            },
            deleteRecords(records) {
                Ext.iterate(records, (record) => {
                    record.remove();
                });
            },
        }, me));

        me = GibsonOS.decorator.Panel.init(me);

        if (typeof(me.getColumns) === 'function') {
            me.columns = me.getColumns();
        }

        me.callParent();

        GibsonOS.decorator.Panel.addListeners(me);
    }
});