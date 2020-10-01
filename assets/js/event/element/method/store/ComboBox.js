Ext.define('GibsonOS.module.core.event.element.method.store.ComboBox', {
    extend: 'GibsonOS.data.Store',
    model: 'GibsonOS.module.core.event.element.method.model.ComboBox',
    autoLoad: false,
    constructor: function(data) {
        let me = this;

        me.proxy = {
            type: 'gosDataProxyAjax',
            url: baseDir + 'core/event/methods'
        };

        me.callParent(arguments);

        return me;
    }
});