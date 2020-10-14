GibsonOS.define('GibsonOS.decorator.action.Add', {
    init: (component) => {
        component = Ext.merge(component, Ext.merge({
            addFunction: null,
            addButton: {
                text: 'Hinzufügen',
                tbarText: null,
                itemId: 'addButton',
                iconCls: 'icon_system system_add',
                listeners: {
                    click: () => {
                        component.addFunction();
                    }
                }
            },
        }, component));

        if (typeof (component.addFunction) === 'function') {
            component.addAction(component.addButton);
        }

        return component;
    }
});