Ext.define('GibsonOS.module.system.user.Form', {
    extend: 'GibsonOS.form.Panel',
    alias: ['widget.gosModuleSystemUserForm'],
    requiredPermission: {
        module: 'system',
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
            itemId: 'systemUserFormUsername',
            name: 'username',
            fieldLabel: 'Benutzername',
            requiredPermission: {
                action: 'save',
                permission: GibsonOS.Permission.WRITE + GibsonOS.Permission.MANAGE
            }
        },{
            xtype: 'gosFormTextfield',
            itemId: 'systemUserFormHost',
            name: 'host',
            fieldLabel: 'Host',
            requiredPermission: {
                action: 'save',
                permission: GibsonOS.Permission.WRITE + GibsonOS.Permission.MANAGE
            }
        },{
            xtype: 'gosFormTextfield',
            itemId: 'systemUserFormIp',
            name: 'ip',
            fieldLabel: 'IP',
            requiredPermission: {
                action: 'save',
                permission: GibsonOS.Permission.WRITE + GibsonOS.Permission.MANAGE
            }
        },{
            xtype: 'gosFormTextfield',
            name: 'password',
            fieldLabel: 'Passwort',
            inputType: 'password',
            requiredPermission: {
                action: 'save',
                permission: permissionWrite
            }
        },{
            xtype: 'gosFormTextfield',
            name: 'passwordRepeat',
            fieldLabel: 'Passwort widerholen',
            inputType: 'password',
            requiredPermission: {
                action: 'save',
                permission: permissionWrite
            }
        }];
        this.buttons = [{
            xtype: 'gosButton',
            requiredPermission: {
                action: 'save',
                permission: permissionWrite
            },
            text: 'Speichern',
            handler: function() {
                this.up('form').getForm().submit({
                    xtype: 'gosFormActionAction',
                    params: {
                        user: form.gos.data.userId ? form.gos.data.userId : 0,
                        add: form.gos.data.add ? form.gos.data.add : false
                    },
                    url: baseDir + 'core/user/save',
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