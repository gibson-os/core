GibsonOS.define('GibsonOS.decorator.PagingBar', {
    init(component) {
        component = Ext.merge(component, Ext.merge({
            enablePagingBar: false,
        }, component));

        if (component.enablePagingBar) {
            if (!component.dockedItems) {
                component.dockedItems = [];
            }

            component.dockedItems.push(new GibsonOS.module.core.component.toolbar.Paging({
                dock: 'bottom',
                store: component.store,
                displayInfo: true
            }));

            component.store.on('add', (store, records) => {
                store.totalCount += records.length;
                grid.down('gosCoreComponentToolbarPaging').onLoad();
            }, component, {
                priority: 999
            });

            component.store.on('remove', (store) => {
                store.totalCount--;
                grid.down('gosCoreComponentToolbarPaging').onLoad();
            }, component, {
                priority: 999
            });
        }

        return component;
    }
});