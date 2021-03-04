Ext.define('GibsonOS.core.component.form.field.DateTime', {
    extend: 'GibsonOS.core.component.form.FieldContainer',
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
        const date = me.down('gosCoreComponentFormFieldDate').getValue();
        const time = me.down('gosCoreComponentFormFieldTime').getValue();

        return new Date(date.getTime() + (time.getHours()*60000*60) + (time.getMinutes()*60000) + (time.getSeconds()*1000));
    }
});
