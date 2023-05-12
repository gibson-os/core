Ext.define('GibsonOS.module.core.icon.store.Icon', {
    extend: 'GibsonOS.data.Store',
    alias: ['coreIconIconStore'],
    model: 'GibsonOS.module.core.icon.model.Icon',
    autoLoad: true,
    proxy: {
        type: 'gosDataProxyAjax',
        url: baseDir + 'core/icon',
        method: 'GET'
    }
});