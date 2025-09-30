GibsonOS.define('GibsonOS.decorator.AutoReload', {
    init: (component) => {
        component = Ext.merge(component, Ext.merge({
            autoReload: false,
            autoReloadDelay: 1000,
            activateAutoReload: function() {
                if (typeof(component.getStore) === 'function') {
                    component.getStore().gos = {
                        autoReload: component.autoReload,
                        autoReloadDelay: component.autoReloadDelay,
                    };
                }
            },
            deactivateAutoReload: function() {
                if (typeof(component.getStore) === 'function') {
                    component.getStore().gos.autoReload = false;
                }
            }
        }, component));

        return component;
    },
    addListeners: (component) => {
        component.on('enable', component.activateAutoReload);
        component.on('show', component.activateAutoReload);

        component.on('close', component.deactivateAutoReload);
        component.on('hide', component.deactivateAutoReload);
        component.on('destroy', component.deactivateAutoReload);
        component.on('disable', component.deactivateAutoReload);
    }
});