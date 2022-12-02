Ext.define('GibsonOS.module.core.component.form.Window', {
    extend: 'GibsonOS.Window',
    alias: ['widget.gosCoreComponentFormWindow'],
    autoHeight: true,
    url: null,
    params: {},
    initComponent() {
        const me = this;

        me.items = [{
            xtype: 'gosCoreComponentFormPanel',
            url: me.url,
            params: me.params,
        }];

        me.callParent();
    }
});