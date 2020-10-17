GibsonOS.define('GibsonOS.decorator.ActionManager', {
    init: (component) => {
        component = Ext.merge(component, Ext.merge({
            enableToolbar: false,
            enableKeyEvents: false,
            enableClickEvents: false,
            enableContextMenu: false,
            itemContextMenu: [],
            containerContextMenu: [],
            actions: [],
            actionKeyEvents: {},
            singleClickAction: null,
            doubleClickAction: null,
        }, component));
        let toolbar = null;
        let containerContextMenu = null;
        let itemContextMenu = null;

        component.addAction = (button) => {
            button = Ext.merge({
                addToToolbar: true,
                addToContainerContextMenu: !button.selectionNeeded,
                addToItemContextMenu: true,
                selectionNeeded: false,
                disabled: button.selectionNeeded,
                enableSingleClick: false,
                enableDoubleClick: false,
                tbarText: null,
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

            if (component.enableClickEvents) {
                if (button.enableSingleClick) {
                    component.singleClickAction = new Ext.Component(button);
                }

                if (button.enableDoubleClick) {
                    component.doubleClickAction = new Ext.Component(button);
                }
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
        component.getSelectionModel().on('selectionchange', function(selection, records) {
            let selectionChangeFunction = (item) => {
                if (item.selectionNeeded) {
                    item.enable(!!records.length);
                }
            };

            if (component.enableToolbar) {
                component.down('toolbar').items.each(selectionChangeFunction);
            }

            if (component.enableContextMenu) {
                component.itemContextMenu.items.each(selectionChangeFunction);
                component.containerContextMenu.items.each(selectionChangeFunction);
            }
        });

        if (component.enableContextMenu) {
            component.on('itemcontextmenu', (grid, record, item, index, event) => {
                component.itemContextMenu.record = record;
                event.stopEvent();
                component.itemContextMenu.showAt(event.getXY());
            });

            component.on('containercontextmenu', (grid, event) => {
                event.stopEvent();
                component.containerContextMenu.showAt(event.getXY());
            });
        }

        if (component.enableKeyEvents) {
            component.on('cellkeydown', (table, td, cellIndex, record, tr, rowIndex, event) => {
                let button = component.actionKeyEvents[event.getKey()];

                if (!button) {
                    return;
                }

                button.fireEvent('click', [button]);
            });
        }

        if (component.enableClickEvents) {
            if (component.singleClickAction) {
                component.on('itemclick', () => {
                    component.singleClickAction.fireEvent('click', [component.singleClickAction]);
                });
            }

            if (component.doubleClickAction) {
                component.on('itemdblclick', () => {
                    component.doubleClickAction.fireEvent('click', [component.doubleClickAction]);
                });
            }
        }
    }
});