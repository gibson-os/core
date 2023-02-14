Ext.define('GibsonOS.module.core.module.store.user.Permission', {
    extend: 'GibsonOS.data.Store',
    alias: ['coreModuleUserPermissionStore'],
    autoLoad: false,
    pageSize: 100,
    proxy: {
        type: 'gosDataProxyAjax',
        url: baseDir + 'core/user/permissions'
    },
    model: 'GibsonOS.module.core.module.model.user.Permission'
});