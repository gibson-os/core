Ext.define('GibsonOS.module.system.user.login.Window', {
    extend: 'GibsonOS.Window',
    alias: ['widget.gosModuleSystemUserLoginWindow'],
    title: 'Login',
    width: 400,
    y: 150,
    modal: true,
    closable: false,
    height: 121,
    resizable: false,
    initComponent: function() {
        this.renderTo = body;
        this.items = [{
            layout: 'column',
            items: [{
                bodyCls: 'icon64 icon_logo',
                width: 64,
                height: 64,
                margin: 15
            },{
                xtype: 'gosModuleSystemUserLoginForm',
                columnWidth: 1
            }]
        }];

        this.callParent();
    }
});