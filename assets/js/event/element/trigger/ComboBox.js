Ext.define('GibsonOS.module.core.event.element.trigger.ComboBox', {
    extend: 'GibsonOS.form.ComboBox',
    alias: ['widget.gosModuleCoreEventElementTriggerComboBox'],
    emptyText: 'Bitte auswählen',
    displayField: 'title',
    valueField: 'trigger',
    requiredPermission: {
        module: 'core',
        task: 'event',
        action: 'methods',
        permission: GibsonOS.Permission.READ
    },
    initComponent: function() {
        let me = this;

        me.store = new GibsonOS.module.core.event.element.trigger.store.ComboBox();

        me.callParent();
    }
});