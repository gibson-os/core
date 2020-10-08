GibsonOS.define('GibsonOS.decorator.contextMenu', {
    init: (component) => {
        component = Ext.merge(component, Ext.merge({
            enableContextMenu: true,
            itemContextMenu: [],
            containerContextMenu: []
        }, component));

        return component;
    },
    addListeners: (component) => {
        if (!component.enableContextMenu) {
            return;
        }

        if (component.itemContextMenu) {
            component.itemContextMenu = new GibsonOS.contextMenu.ContextMenu({
                items: component.itemContextMenu,
                parent: component
            });
        }

        component.on('itemcontextmenu', function(grid, record, item, index, event) {
            if (component.itemContextMenu) {
                component.itemContextMenu.record = record;
                event.stopEvent();
                component.itemContextMenu.showAt(event.getXY());
            }
        });

        if (component.containerContextMenu) {
            component.containerContextMenu = new GibsonOS.contextMenu.ContextMenu({
                items: component.containerContextMenu,
                parent: component
            });
        }

        component.on('containercontextmenu', function(grid, event) {
            if (component.containerContextMenu) {
                event.stopEvent();
                component.containerContextMenu.showAt(event.getXY());
            }
        });
    }
});