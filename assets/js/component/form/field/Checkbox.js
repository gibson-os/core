Ext.define('GibsonOS.module.core.component.form.field.Checkbox', {
    extend: 'Ext.form.field.Checkbox',
    alias: ['widget.gosCoreComponentFormFieldCheckbox'],
    anchor: '100%',
    border: false,
    inputValue: true,
    uncheckedValue: false,
    initComponent() {
        const me = this;

        me.callParent();

        GibsonOS.decorator.FormField.addListeners(me);
    }
});
