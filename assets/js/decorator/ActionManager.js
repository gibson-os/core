GibsonOS.define('GibsonOS.decorator.ActionManager', {
    init: (component) => {
        component = Ext.merge(component, Ext.merge({
            enableToolbar: false,
            enableKeyEvents: false,
            enableContextMenu: false,
            itemContextMenu: [],
            containerContextMenu: [],
            actions: [],
            actionKeyEvents: {}
        }, component));
        let toolbar = null;
        let containerContextMenu = null;
        let itemContextMenu = null;

        component.addAction = (button) => {
            button = Ext.merge({
                addToToolbar: true,
                addToContainerContextMenu: true,
                addToItemContextMenu: true
            }, button);
            if (component.enableToolbar && button.addToToolbar) {
                toolbar.add(Ext.merge(Ext.clone(button), {text: button.tbarText ?? null}));
            }

            if (component.enableContextMenu) {
                if (button.addToContainerContextMenu) {
                    containerContextMenu.add(button);
                }

                if (button.addToItemContextMenu) {
                    itemContextMenu.add(button);
                }
            }

            if (component.enableKeyEvents && button.keyEvent) {
                component.actionKeyEvents[button.keyEvent] = new Ext.Component(button);
            }
        };

        if (component.enableToolbar) {
            toolbar = new Ext.toolbar.Toolbar();

            if (!component.dockedItems) {
                component.dockedItems = [];
            }

            component.dockedItems.push(toolbar);
        }

        if (component.enableContextMenu) {
            itemContextMenu = new GibsonOS.contextMenu.ContextMenu({
                items: component.itemContextMenu,
                parent: component
            });
            component.itemContextMenu = itemContextMenu;

            containerContextMenu = new GibsonOS.contextMenu.ContextMenu({
                items: component.containerContextMenu,
                parent: component
            });
            component.containerContextMenu = containerContextMenu;
        }

        return component;
    },
    addListeners: (component) => {
        if (component.enableContextMenu) {
            component.on('itemcontextmenu', function(grid, record, item, index, event) {
                component.itemContextMenu.record = record;
                event.stopEvent();
                component.itemContextMenu.showAt(event.getXY());
                console.log(component.itemContextMenu);
            });

            component.on('containercontextmenu', function(grid, event) {
                event.stopEvent();
                component.containerContextMenu.showAt(event.getXY());
            });
        }

        if (component.enableKeyEvents) {
            component.on('cellkeydown', function(table, td, cellIndex, record, tr, rowIndex, event) {
                console.log(component.actionKeyEvents);
                let button = component.actionKeyEvents[event.getKey()];

                if (!button) {
                    return;
                }

                button.fireEvent('click', [button]);
            });
        }
    }
});