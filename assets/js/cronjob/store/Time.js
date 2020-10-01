Ext.define('GibsonOS.module.core.cronjob.store.Time', {
    extend: 'GibsonOS.data.Store',
    alias: ['store.gosModuleCoreCronjobTimeStore'],
    model: 'GibsonOS.module.core.cronjob.model.Time',
    constructor: function(data) {
        let me = this;

        me.autoLoad = !!data.gos.data.cronjob;
        me.proxy = {
            type: 'gosDataProxyAjax',
            url: baseDir + 'core/cronjob/times',
            extraParams: {
                moduleId: data.gos.data.cronjob ? data.gos.data.cronjob.id : 0
            }
        };

        me.callParent(arguments);

        return me;
    }
});