Ext.define('GibsonOS.module.core.module.store.role.Permission', {
    extend: 'GibsonOS.data.Store',
    alias: ['coreModuleRolePermissionStore'],
    autoLoad: false,
    pageSize: 100,
    proxy: {
        type: 'gosDataProxyAjax',
        url: baseDir + 'core/role/permission'
    },
    model: 'GibsonOS.module.core.module.model.role.Permission'
});