Ext.define('GibsonOS.module.system.user.host.Form', {
    extend: 'GibsonOS.form.Panel',
    alias: ['widget.gosModuleSystemUserHostForm'],
    itemId: 'systemUserHostForm',
    initComponent: function() {
        this.items = [{
            xtype: 'gosFormTextfield',
            fieldLabel: 'Host',
            name: 'host'
        },{
            xtype: 'gosFormTextfield',
            fieldLabel: 'IP',
            name: 'ip'
        }];
        this.buttons = [{
            xtype: 'gosButton',
            text: 'Speichern',
            handler: function() {
                var form = this.up('form');
                form.getForm().submit({
                    xtype: 'gosFormActionAction',
                    params: {
                        user: form.gos.data.userId ? form.gos.data.userId : 0
                    },
                    url: baseDir + 'system/user/savehost',
                    success: function(formAction, action) {
                        if (form.gos.data.success) {
                            form.gos.data.success(form, action);
                        }
                    },
                    failure: function(form, action) {
                        GibsonOS.MessageBox.show({msg: 'Host konnte nicht hinzugefügt werden!'});
                    }
                });
            }
        }];

        this.callParent();
    }
});