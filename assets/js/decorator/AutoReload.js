GibsonOS.define('GibsonOS.decorator.AutoReload', {
    init: (component) => {
        component = Ext.merge(component, Ext.merge({
            autoReload: false,
            activateAutoReload: function() {
                if (component.getStore()) {
                    component.getStore().gos.autoReload = component.autoReload;
                }
            },
            deactivateAutoReload: function() {
                if (component.getStore()) {
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