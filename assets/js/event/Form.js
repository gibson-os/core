Ext.define('GibsonOS.module.core.event.Form', {
    extend: 'GibsonOS.form.Panel',
    alias: ['widget.gosModuleCoreEventForm'],
    itemId: 'coreEventForm',
    defaults: {
        border: false,
        xtype: 'panel',
        flex: 1,
        layout: 'anchor'
    },
    border: false,
    layout: 'hbox',
    initComponent: function () {
        let me = this;

        me.items = [{
            xtype: 'gosFormHidden',
            name: 'id'
        },{
            xtype: 'gosFormTextfield',
            fieldLabel: 'Name',
            name: 'name'
        },{
            xtype: 'gosFormCheckbox',
            name: 'async',
            margins: '0 5px',
            boxLabel: 'Asynchron'
        },{
            xtype: 'gosFormCheckbox',
            name: 'active',
            boxLabel: 'Aktiv'
        }];

        this.callParent();
    }
});