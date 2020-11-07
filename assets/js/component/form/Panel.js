Ext.define('GibsonOS.core.component.form.Panel', {
    extend: 'Ext.form.Panel',
    alias: ['widget.gosCoreComponentFormPanel'],
    buttonAlign: 'center',
    frame: true,
    flex: 1,
    defaults: {
        xtype: 'gosCoreComponentPanel'
    },
    enableToolbar: true,
    enableKeyEvents: true,
    enableClickEvents: false,
    enableContextMenu: false,
    initComponent: function() {
        let me = this;

        me = GibsonOS.decorator.Panel.init(me);

        me.callParent();

        GibsonOS.decorator.Panel.addListeners(me);

        me.getForm().on('actioncomplete', function(form, action) {
            let responseText = Ext.decode(action.response.responseText);
            GibsonOS.checkResponseForLogin(responseText);
            GibsonOS.checkResponseForErrorMessage(responseText, action);

            form.getFields().each(function(field) {
                field.originalValue = field.getValue();
            });
        }, this, {
            priority: 999
        });
        me.getForm().on('actionfailed', function(form, action) {
            let responseText = Ext.decode(action.response.responseText);
            GibsonOS.checkResponseForLogin(responseText);
            GibsonOS.checkResponseForErrorMessage(responseText, action);
        }, this, {
            priority: 999
        });
    }
});