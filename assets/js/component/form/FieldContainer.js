Ext.define('GibsonOS.module.core.component.form.FieldContainer', {
    extend: 'Ext.form.FieldContainer',
    alias: ['widget.gosCoreComponentFormFieldContainer'],
    anchor: '100%',
    fieldLabel: 'Felder',
    layout: 'hbox',
    defaults: {
        flex: 1,
        hideLabel: true
    }
});