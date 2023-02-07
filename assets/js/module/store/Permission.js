Ext.define('GibsonOS.module.core.module.store.Permission', {
    extend: 'GibsonOS.data.Store',
    alias: ['coreModulePermissionStore'],
    autoLoad: false,
    pageSize: 100,
    proxy: {
        type: 'gosDataProxyAjax',
        url: baseDir + 'core/module/permission'
    },
    model: 'GibsonOS.module.core.module.model.Permission'
});