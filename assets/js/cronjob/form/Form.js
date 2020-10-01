Ext.define('GibsonOS.module.core.crontab.form.Form', {
    extend: 'GibsonOS.form.Panel',
    alias: ['widget.gosModuleCoreCronjobForm'],
    requiredPermission: {
        module: 'core',
        task: 'crontab',
        action: 'save'
    },
    initComponent: function() {
        let me = this;

        me.items = [{
            xtype: 'gosFormTextfield',
            name: 'command',
            fieldLabel: 'Kommando'
        },{
            xtype: 'gosFormTextfield',
            name: 'user',
            fieldLabel: 'Benutzer'
        }];
        me.buttons = [{
            xtype: 'gosButton',
            requiredPermission: {
                permission: GibsonOS.Permission.WRITE
            },
            text: 'Speichern',
            handler: function() {
                this.up('form').getForm().submit({
                    xtype: 'gosFormActionAction',
                    params: {
                        id: form.gos.data.cronjob ? form.gos.data.cronjob.id : 0
                    },
                    url: baseDir + 'core/cronjob/save',
                    success: function(formAction, action) {
                        if (form.gos.data.success) {
                            form.gos.data.success(form, action);
                        }
                    }
                });
            }
        }];

        this.callParent();
    }
});