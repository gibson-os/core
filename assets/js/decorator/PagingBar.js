GibsonOS.define('GibsonOS.decorator.PagingBar', {
    init(component) {
        component = Ext.merge(component, Ext.merge({
            enablePagingBar: false,
            viewItem: component,
        }, component));

        if (component.enablePagingBar) {
            if (!component.dockedItems) {
                component.dockedItems = [];
            }

            component.dockedItems.push(new GibsonOS.module.core.component.toolbar.Paging({
                dock: 'bottom',
                store: component.viewItem.store,
                displayInfo: true
            }));

            component.viewItem.store.on('add', (store, records) => {
                store.totalCount += records.length;
                component.down('gosCoreComponentToolbarPaging').onLoad();
            }, component, {
                priority: 999
            });

            component.viewItem.store.on('remove', (store) => {
                store.totalCount--;
                component.down('gosCoreComponentToolbarPaging').onLoad();
            }, component, {
                priority: 999
            });
        }

        return component;
    }
});