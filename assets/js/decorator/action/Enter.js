GibsonOS.define('GibsonOS.decorator.action.Enter', {
    init: (component) => {
        component = Ext.merge(component, Ext.merge({
            enterFunction: null,
            enterButton: {
                text: 'Bearbeiten',
                itemId: 'enterButton',
                iconCls: 'icon_system system_edit',
                keyEvent: Ext.EventObject.ENTER,
                selectionNeeded: true,
                enableDoubleClick: true,
                listeners: {
                    click: () => {
                        const viewItem = component.viewItem ?? component;
                        component.enterFunction(viewItem.getSelectionModel().getSelection()[0]);
                    }
                }
            },
        }, component));

        if (typeof(component.enterFunction) === 'function') {
            component.addAction(component.enterButton);
        }

        return component;
    }
});