Ext.define('GibsonOS.module.core.event.element.command.ComboBox', {
    extend: 'GibsonOS.form.ComboBox',
    alias: ['widget.gosModuleCoreEventElementCommandComboBox'],
    emptyText: 'Bitte auswählen',
    displayField: 'name',
    valueField: 'command',
    initComponent: function() {
        let me = this;

        me.store = new GibsonOS.module.core.event.element.command.store.ComboBox();

        me.callParent();
    }
});