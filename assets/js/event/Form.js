Ext.define('GibsonOS.module.core.event.Form', {
    extend: 'GibsonOS.form.Panel',
    alias: ['widget.gosModuleCoreEventForm'],
    itemId: 'coreEventForm',
    border: false,
    initComponent: function () {
        let me = this;

        me.items = [{
            xtype: 'gosFormHidden',
            name: 'id'
        },{
            xtype: 'gosCoreComponentFormFieldContainer',
            fieldLabel: 'Name',
            items: [{
                xtype: 'gosFormTextfield',
                flex: 2,
                name: 'name'
            },{
                xtype: 'gosFormCheckbox',
                name: 'active',
                boxLabel: 'Aktiv',
                margins: '0 5px',
                uncheckedValue: false
            }]
        },{
            xtype: 'gosCoreComponentFormFieldContainer',
            fieldLabel: '&nbsp;',
            labelSeparator: '',
            items: [{
                xtype: 'gosFormCheckbox',
                name: 'async',
                boxLabel: 'Asynchron',
                uncheckedValue: false
            },{
                xtype: 'gosFormCheckbox',
                name: 'exitOnError',
                boxLabel: 'Bei Fehler beenden',
                uncheckedValue: false
            },{
                xtype: 'gosFormCheckbox',
                name: 'lockCommand',
                margins: '0 5px',
                boxLabel: 'Nicht mehrfach gleichzeitig ausführen',
                uncheckedValue: false
            }],
        }];

        me.callParent();
    }
});