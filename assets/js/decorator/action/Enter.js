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
                maxSelectionAllowed: 1,
                enableDoubleClick: true,
                listeners: {
                    click() {
                        const component = this.component;
                        
                        component.enterFunction(component.viewItem.getSelectionModel().getSelection()[0]);
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