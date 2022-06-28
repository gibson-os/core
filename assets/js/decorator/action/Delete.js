GibsonOS.define('GibsonOS.decorator.action.Delete', {
    init: (component) => {
        component = Ext.merge(component, Ext.merge({
            deleteFunction: null,
            deleteButton: {
                text: 'Löschen',
                itemId: 'deleteButton',
                iconCls: 'icon_system system_delete',
                keyEvent: Ext.EventObject.DELETE,
                selectionNeeded: true,
                listeners: {
                    click: () => {
                        const viewItem = component.viewItem ?? component;
                        component.deleteFunction(
                            typeof viewItem.getSelectionModel === 'function'
                                ? viewItem.getSelectionModel().getSelection()
                                : []
                        );
                    }
                }
            },
        }, component));

        if (typeof(component.deleteFunction) === 'function') {
            component.addAction(component.deleteButton);
        }

        return component;
    }
});