Ext.define('GibsonOS.module.core.crontab.Form', {
    extend: 'GibsonOS.form.Panel',
    alias: ['widget.gosModuleCoreCronjobForm'],
    requiredPermission: {
        module: 'core',
        task: 'cronjob',
        action: '',
        method: 'POST'
    },
    defaults: {
        border: false,
        xtype: 'panel',
        flex: 1,
        layout: 'anchor'
    },
    border: false,
    layout: 'hbox',
    initComponent: function() {
        let me = this;

        me.items = [{
            xtype: 'gosFormHidden',
            name: 'id'
        },{
            xtype: 'gosFormTextfield',
            name: 'command',
            fieldLabel: 'Kommando'
        },{
            xtype: 'gosFormTextfield',
            name: 'user',
            fieldLabel: 'Benutzer',
            margins: '0 0 0 5px',
        }];

        me.callParent();
    }
});