Ext.define('GibsonOS.module.system.user.store.User', {
    extend: 'GibsonOS.data.Store',
    alias: ['store.gosModuleSystemUserStore'],
    model: 'GibsonOS.module.system.user.model.User',
    proxy: {
        type: 'gosDataProxyAjax',
        url: baseDir + 'core/user/index'
    }
});