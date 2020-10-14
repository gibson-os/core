GibsonOS.define('GibsonOS.decorator.action.Delete', {
    init: (component) => {
        component = Ext.merge(component, Ext.merge({
            deleteFunction: null,
            deleteButton: {
                text: 'Löschen',
                tbarText: null,
                itemId: 'deleteButton',
                iconCls: 'icon_system system_delete',
                disabled: true,
                addToContainerContextMenu: false,
                listeners: {
                    click: () => {
                        component.deleteFunction();
                    }
                }
            },
        }, component));

        if (typeof(component.deleteFunction) === 'function') {
            component.addAction(component.deleteButton);
        }

        return component;
    },
    addListeners: (component) => {
        component.getSelectionModel().on('selectionchange', function(selection, records, options) {
            let tbarButton = component.down('toolbar').down('#deleteButton');
            let contextMenuButton = component.itemContextMenu.down('#deleteButton');

            if (selection.getCount() === 0) {
                if (tbarButton) {
                    tbarButton.disable()
                }

                if (contextMenuButton) {
                    contextMenuButton.disable()
                }
            } else {
                if (tbarButton) {
                    tbarButton.enable()
                }

                if (contextMenuButton) {
                    contextMenuButton.enable()
                }
            }
        });

        if (typeof(component.deleteFunction) === 'function') {
            component.on('cellkeydown', function(table, td, cellIndex, record, tr, rowIndex, event) {
                if (event.getKey() === Ext.EventObject.DELETE) {
                    component.deleteFunction();
                }
            });
        }
    }
});