Ext.define('GibsonOS.module.core.user.store.User', {
    extend: 'GibsonOS.data.Store',
    alias: ['store.gosModuleCoreUserStore'],
    model: 'GibsonOS.module.core.user.model.User',
    proxy: {
        type: 'gosDataProxyAjax',
        url: baseDir + 'core/user/index'
    }
});