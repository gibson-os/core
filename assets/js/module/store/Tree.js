Ext.define('GibsonOS.module.core.module.store.Tree', {
    extend: 'GibsonOS.data.TreeStore',
    alias: ['coreModuleTreeStore'],
    autoLoad: true,
    model: 'GibsonOS.module.core.module.model.Module',
    proxy: {
        type: 'gosDataProxyAjax',
        url: baseDir + 'core/module',
        method: 'GET'
    }
});