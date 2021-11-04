Ext.define('GibsonOS.module.core.user.login.Form', {
    extend: 'GibsonOS.form.Panel',
    alias: ['widget.gosModuleCoreUserLoginForm'],
    standardSubmit: true,
    frame: true,
    buttonAlign: 'right',
    url: baseDir + 'core/user/login',
    initComponent: function() {
        var keyupListener = function(field, event) {
            if (event.getKey() === Ext.EventObject.RETURN) {
                this.up('form').getForm().submit();
            }
        };

        this.items = [{
            xtype: 'gosFormTextfield',
            name: 'username',
            fieldLabel: 'Benutzername',
            value: getJsonValue(request, 'username'),
            enableKeyEvents: true,
            listeners: {
                keyup: keyupListener,
                render: function(field) {
                    field.focus(true, 1000);
                }
            }
        },{
            xtype: 'gosFormTextfield',
            name: 'password',
            fieldLabel: 'Passwort',
            inputType: 'password',
            enableKeyEvents: true,
            listeners: {
                keyup: keyupListener
            }
        }];
        this.buttons = [{
            xtype: 'gosButton',
            text: 'Login',
            handler: function() {
                this.up('form').getForm().submit();
            }
        }];

        this.callParent();
    }
});