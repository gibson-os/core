Ext.define('GibsonOS.module.core.component.form.Window', {
    extend: 'GibsonOS.Window',
    alias: ['widget.gosCoreComponentFormWindow'],
    autoHeight: true,
    url: null,
    params: {},
    initComponent() {
        this.items = [{
            xtype: 'gosCoreComponentFormPanel',
            url: url,
            params: params,
        }];

        this.callParent();
    }
});