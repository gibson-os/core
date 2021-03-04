Ext.define('GibsonOS.module.core.event.element.operator.ComboBox', {
    extend: 'GibsonOS.form.ComboBox',
    alias: ['widget.gosModuleCoreEventElementOperatorComboBox'],
    emptyText: 'Keiner',
    displayField: 'name',
    valueField: 'operator',
    exclude: [],
    initComponent: function() {
        let me = this;

        me.store = new GibsonOS.module.core.event.element.operator.store.ComboBox({exclude: me.exclude});

        me.callParent();
    }
});