GibsonOS.define('GibsonOS.decorator.ActionManager', {
    init: (component) => {
        component = Ext.merge(component, Ext.merge({
            enableToolbar: false,
            enableKeyEvents: false,
            enableClickEvents: false,
            enableContextMenu: false,
            viewItem: component,
            itemContextMenu: [],
            containerContextMenu: [],
            actions: [],
            actionKeyEvents: {},
            singleClickAction: null,
            doubleClickAction: null,
            setActionDisabled: (selector, disabled) => {
                if (component.down(selector)) {
                    component.down(selector).setDisabled(disabled);
                }

                if (component.viewItem.itemContextMenu.down(selector)) {
                    component.viewItem.itemContextMenu.down(selector).setDisabled(disabled);
                }

                if (component.viewItem.containerContextMenu.down(selector)) {
                    component.viewItem.containerContextMenu.down(selector).setDisabled(disabled);
                }
            },
            enableAction: (selector) => {
                component.setActionDisabled(selector, false);
            },
            disableAction: (selector) => {
                component.setActionDisabled(selector, true);
            }
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
                minSelectionNeeded: 1,
                disabled: button.selectionNeeded ?? false,
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
                parent: component.viewItem
            });
            component.viewItem.itemContextMenu = itemContextMenu;

            containerContextMenu = new GibsonOS.contextMenu.ContextMenu({
                items: component.containerContextMenu,
                parent: component.viewItem
            });
            component.viewItem.containerContextMenu = containerContextMenu;
        }

        return component;
    },
    addListeners: (component) => {
        if (typeof(component.viewItem.getSelectionModel) === 'function') {
            component.viewItem.getSelectionModel().on('selectionchange', function(selection, records) {
                let selectionChangeFunction = (item) => {
                    if (item.selectionNeeded) {
                        item.setDisabled(records.length < item.minSelectionNeeded);
                    }
                };

                if (component.enableToolbar) {
                    component.down('toolbar').items.each(selectionChangeFunction);
                }

                if (component.enableContextMenu) {
                    component.viewItem.itemContextMenu.items.each(selectionChangeFunction);
                    component.viewItem.containerContextMenu.items.each(selectionChangeFunction);
                }
            });
        }

        if (component.enableContextMenu) {
            component.viewItem.on('itemcontextmenu', (grid, record, item, index, event) => {
                let selectionModel = component.viewItem.getSelectionModel();

                component.viewItem.itemContextMenu.record = record;

                if (!selectionModel.isSelected(record)) {
                    selectionModel.select([record]);
                }

                event.stopEvent();
                component.viewItem.itemContextMenu.showAt(event.getXY());
            });

            component.viewItem.on('containercontextmenu', (grid, event) => {
                event.stopEvent();
                component.viewItem.containerContextMenu.showAt(event.getXY());
            });
        }

        if (component.enableKeyEvents) {
            const keyEvent = (event) => {
                let button = component.actionKeyEvents[event.getKey()];

                if (
                    !button ||
                    (button.selectionNeeded && component.viewItem.getSelectionModel().getCount() === 0)
                ) {
                    return;
                }

                button.fireEvent('click', [button]);
            };

            component.viewItem.on('containerkeydown', (view, event) => keyEvent(event));
            component.viewItem.on('itemkeydown', (view, record, item, index, event) => keyEvent(event));
            component.viewItem.on('cellkeydown', (view, td, cellIndex, record, tr, rowIndex, event) => keyEvent(event));
        }

        if (component.enableClickEvents) {
            if (component.singleClickAction) {
                component.viewItem.on('itemclick', () => {
                    component.singleClickAction.fireEvent('click', [component.singleClickAction]);
                });
            }

            if (component.doubleClickAction) {
                component.viewItem.on('itemdblclick', () => {
                    component.doubleClickAction.fireEvent('click', [component.doubleClickAction]);
                });
            }
        }
    }
});