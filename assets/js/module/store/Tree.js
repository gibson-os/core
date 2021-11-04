Ext.define('GibsonOS.module.core.module.store.Tree', {
    extend: 'GibsonOS.data.TreeStore',
    alias: ['coreModuleTreeStore'],
    autoLoad: true,
    proxy: {
        type: 'gosDataProxyAjax',
        url: baseDir + 'system/module/index'
    }
});