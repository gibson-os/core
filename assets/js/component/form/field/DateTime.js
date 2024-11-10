Ext.define('GibsonOS.module.core.component.form.field.DateTime', {
    extend: 'GibsonOS.module.core.component.form.FieldContainer',
    alias: ['widget.gosCoreComponentFormFieldDateTime'],
    fieldLabel: 'Datum und Zeit',
    combinedField: true,
    value: null,
    initComponent() {
        const me = this;

        me.items = [{
            xtype: 'gosCoreComponentFormFieldDate',
            name: me.name + 'Date',
            margins: '0 5px 0 0',
            value: me.value
        },{
            xtype: 'gosCoreComponentFormFieldTime',
            name: me.name + 'Time',
            value: me.value
        }];

        me.callParent();
    },
    getName() {
        return this.name;
    },
    getValue() {
        const me = this;
        let date = me.down('gosCoreComponentFormFieldDate').getValue();
        let time = me.down('gosCoreComponentFormFieldTime').getValue();

        if (date === null && time === null) {
            return null;
        }

        date = date ?? new Date();
        time = time ?? new Date();

        return new Date(date.getTime() + (time.getHours()*60000*60) + (time.getMinutes()*60000) + (time.getSeconds()*1000));
    },
    setValue(value) {
        const me = this;
        const date = new Date(value);

        me.down('gosCoreComponentFormFieldDate').setValue(date);
        me.down('gosCoreComponentFormFieldTime').setValue(date);
    }
});
