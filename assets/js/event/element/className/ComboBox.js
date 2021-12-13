Ext.define('GibsonOS.module.core.event.element.className.ComboBox', {
    extend: 'GibsonOS.form.ComboBox',
    alias: ['widget.gosModuleCoreEventElementClassNameComboBox'],
    emptyText: 'Bitte auswählen',
    displayField: 'title',
    valueField: 'className',
    requiredPermission: {
        module: 'core',
        task: 'event',
        action: 'classNames',
        permission: GibsonOS.Permission.READ
    },
    initComponent: function() {
        let me = this;

        me.store = new GibsonOS.module.core.event.element.className.store.ComboBox();

        me.callParent();
    }
});