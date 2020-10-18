Ext.define('GibsonOS.module.core.cronjob.store.Time', {
    extend: 'GibsonOS.data.Store',
    alias: ['store.gosModuleCoreCronjobTimeStore'],
    model: 'GibsonOS.module.core.cronjob.model.Time',
    autoLoad: false,
    constructor: function(data) {
        let me = this;

        me.proxy = {
            type: 'gosDataProxyAjax',
            url: baseDir + 'core/cronjob/times'
        };

        me.callParent(arguments);

        return me;
    }
});