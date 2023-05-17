Ext.define('GibsonOS.module.core.event.element.method.ComboBox', {
    extend: 'GibsonOS.form.ComboBox',
    alias: ['widget.gosModuleCoreEventElementMethodComboBox'],
    emptyText: 'Bitte auswählen',
    displayField: 'title',
    valueField: 'method',
    requiredPermission: {
        module: 'core',
        task: 'event',
        action: 'methods',
        method: 'GET',
        permission: GibsonOS.Permission.READ
    },
    initComponent: function() {
        let me = this;

        me.store = new GibsonOS.module.core.event.element.method.store.ComboBox();

        me.callParent();
    }
});