Ext.define('GibsonOS.module.core.event.element.trigger.store.ComboBox', {
    extend: 'GibsonOS.data.Store',
    model: 'GibsonOS.module.core.event.element.trigger.model.ComboBox',
    autoLoad: false,
    constructor: function(data) {
        let me = this;

        me.proxy = {
            type: 'gosDataProxyAjax',
            url: baseDir + 'core/event/classTriggers'
        };

        me.callParent(arguments);

        return me;
    }
});