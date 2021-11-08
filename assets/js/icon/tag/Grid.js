Ext.define('GibsonOS.module.core.icon.tag.Grid', {
    extend: 'GibsonOS.grid.Panel',
    alias: ['widget.gosModuleCoreIconTagGrid'],
    itemId: 'coreIconTagGrid',
    initComponent: function() {
        this.store = new GibsonOS.module.core.icon.store.Tag();
        this.columns = [{
            header: 'Benutzer',
            dataIndex: 'tag',
            flex: 1
        },{
            header: 'Anzahl',
            dataIndex: 'count',
            align: 'right',
            width: 50
        }];

        this.callParent();
    }
});