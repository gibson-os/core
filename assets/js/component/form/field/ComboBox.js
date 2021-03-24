Ext.define('GibsonOS.module.core.component.form.field.ComboBox', {
    extend: 'Ext.form.field.ComboBox',
    alias: ['widget.gosCoreComponentFormFieldComboBox'],
    anchor: '100%',
    border: false,
    queryMode: 'local',
    displayField: 'name',
    valueField: 'id',
    editable: false,
});
