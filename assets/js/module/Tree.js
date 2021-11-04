Ext.define('GibsonOS.module.core.module.Tree', {
    extend: 'GibsonOS.tree.Panel',
    alias: ['widget.gosModuleCoreModuleTree'],
    itemId: 'coreModuleTree',
    header: false,
    initComponent: function() {
        this.store = new GibsonOS.module.core.module.store.Tree();

        this.callParent();
    }
});