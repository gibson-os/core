Ext.define('GibsonOS.module.core.user.Form', {
    extend: 'GibsonOS.form.Panel',
    alias: ['widget.gosModuleCoreUserForm'],
    requiredPermission: {
        module: 'core',
        task: 'user'
    },
    initComponent: function() {
        var form = this;

        if (!this.gos.data.add) {
            this.title = 'Einstellungen'
        }

        var permissionWrite = GibsonOS.Permission.WRITE;

        if (
            this.gos.data.userId ||
            this.gos.data.add
        ) {
            permissionWrite += GibsonOS.Permission.MANAGE;
        }

        this.items = [{
            xtype: 'gosFormTextfield',
            itemId: 'coreUserFormUsername',
            name: 'user',
            fieldLabel: 'Benutzername',
            requiredPermission: {
                action: '',
                method: 'POST',
                permission: GibsonOS.Permission.WRITE + GibsonOS.Permission.MANAGE
            }
        },{
            xtype: 'gosFormTextfield',
            itemId: 'coreUserFormHost',
            name: 'host',
            fieldLabel: 'Host',
            requiredPermission: {
                action: '',
                method: 'POST',
                permission: GibsonOS.Permission.WRITE + GibsonOS.Permission.MANAGE
            }
        },{
            xtype: 'gosFormTextfield',
            itemId: 'coreUserFormIp',
            name: 'ip',
            fieldLabel: 'IP',
            requiredPermission: {
                action: '',
                method: 'POST',
                permission: GibsonOS.Permission.WRITE + GibsonOS.Permission.MANAGE
            }
        },{
            xtype: 'gosFormTextfield',
            name: 'password',
            fieldLabel: 'Passwort',
            inputType: 'password',
            requiredPermission: {
                action: '',
                method: 'POST',
                permission: permissionWrite
            }
        },{
            xtype: 'gosFormTextfield',
            name: 'passwordRepeat',
            fieldLabel: 'Passwort widerholen',
            inputType: 'password',
            requiredPermission: {
                action: '',
                method: 'POST',
                permission: permissionWrite
            }
        }];
        this.buttons = [{
            xtype: 'gosButton',
            requiredPermission: {
                action: '',
                method: 'POST',
                permission: permissionWrite
            },
            text: 'Speichern',
            handler: function() {
                this.up('form').getForm().submit({
                    xtype: 'gosFormActionAction',
                    params: {
                        id: form.gos.data.userId ? form.gos.data.userId : 0,
                        add: form.gos.data.add ? form.gos.data.add : false
                    },
                    url: baseDir + 'core/user',
                    method: 'POST',
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