Ext.define('GibsonOS.module.core.role.store.Role', {
    extend: 'GibsonOS.data.Store',
    alias: ['store.gosModuleCoreRoleStore'],
    model: 'GibsonOS.module.core.role.model.Role',
    proxy: {
        type: 'gosDataProxyAjax',
        url: baseDir + 'core/role/index'
    }
});