Ext.define('GibsonOS.module.core.role.store.User', {
    extend: 'GibsonOS.data.Store',
    alias: ['store.gosModuleCoreRoleStore'],
    model: 'GibsonOS.module.core.role.model.User',
    autoLoad: false,
    proxy: {
        type: 'gosDataProxyAjax',
        url: baseDir + 'core/role/users'
    }
});