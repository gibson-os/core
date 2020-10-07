Ext.define('GibsonOS.module.core.event.trigger.store.Grid', {
    extend: 'GibsonOS.data.Store',
    alias: ['coreEventTriggerGridStore'],
    model: 'GibsonOS.module.core.event.trigger.model.Grid',
    constructor: function(data) {
        let me = this;

        me.proxy = {
            type: 'gosDataProxyAjax',
            url: baseDir + 'core/event/triggers'
        };

        me.callParent(arguments);

        return me;
    }
});