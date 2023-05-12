Ext.define('GibsonOS.module.core.cronjob.store.Cronjob', {
    extend: 'GibsonOS.data.Store',
    alias: ['store.gosModuleCoreCronjobStore'],
    model: 'GibsonOS.module.core.cronjob.model.Cronjob',
    proxy: {
        type: 'gosDataProxyAjax',
        url: baseDir + 'core/cronjob',
        method: 'GET'
    }
});