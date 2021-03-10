Ext.define('GibsonOS.module.core.component.form.field.DateTime', {
    extend: 'GibsonOS.module.core.component.form.FieldContainer',
    alias: ['widget.gosCoreComponentFormFieldDateTime'],
    fieldLabel: 'Datum und Zeit',
    initComponent() {
        const me = this;

        me.items = [{
            xtype: 'gosCoreComponentFormFieldDate',
            margins: '0 5px 0 0'
        },{
            xtype: 'gosCoreComponentFormFieldTime'
        }];

        me.callParent();
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
    }
});
