Ext.define('GibsonOS.module.core.event.store.Grid', {
    extend: 'GibsonOS.data.Store',
    alias: ['coreEventGridStore'],
    model: 'GibsonOS.module.core.event.model.Grid',
    constructor: function(data) {
        let me = this;

        me.proxy = {
            type: 'gosDataProxyAjax',
            url: baseDir + 'core/event/index'
        };

        me.callParent(arguments);

        return me;
    }
});