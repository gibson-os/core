GibsonOS.define('GibsonOS.decorator.Panel', {
    init: (component) => {
        component = Ext.merge(component, Ext.merge({
            getColumns: null,
        }, component));

        component = GibsonOS.decorator.ActionManager.init(component);
        component = GibsonOS.decorator.AutoReload.init(component);
        component = GibsonOS.decorator.PagingBar.init(component);
        component = GibsonOS.decorator.Drag.init(component);
        component = GibsonOS.decorator.Drop.init(component);
        component = GibsonOS.decorator.action.Add.init(component);
        component = GibsonOS.decorator.action.Enter.init(component);
        component = GibsonOS.decorator.action.Delete.init(component);
        component = GibsonOS.decorator.action.Filter.init(component);

        return component;
    },
    addListeners: (component) => {
        GibsonOS.decorator.ActionManager.addListeners(component);
        GibsonOS.decorator.AutoReload.addListeners(component);
        GibsonOS.decorator.Drag.addListeners(component);
        GibsonOS.decorator.Drop.addListeners(component);
    }
});