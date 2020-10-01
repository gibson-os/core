Ext.define('GibsonOS.module.core.event.element.store.TreeGrid', {
    extend: 'GibsonOS.data.TreeStore',
    alias: ['coreEventElementTreeGridStore'],
    model: 'GibsonOS.module.core.event.element.model.TreeGrid',
    constructor: function(data) {
        let me = this;

        me.proxy = {
            type: 'gosDataProxyAjax',
            url: baseDir + 'core/event/elements'
        };

        me.callParent(arguments);

        return me;
    }
});