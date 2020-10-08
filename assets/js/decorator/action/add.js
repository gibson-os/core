GibsonOS.define('GibsonOS.decorator.action.add', {
    init: (component) => {
        component = Ext.merge(component, Ext.merge({
            addFunction: null,
            itemContextMenu: [],
            containerContextMenu: [],
            tbar: [],
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
            component.tbar.push(Ext.merge(Ext.clone(component.addButton), {text: component.addButton.tbarText}));
            component.itemContextMenu.push(component.addButton);
            component.containerContextMenu.push(component.addButton);
        }

        return component;
    }
});