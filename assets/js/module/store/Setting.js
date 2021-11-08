Ext.define('GibsonOS.module.core.module.store.Setting', {
    extend: 'GibsonOS.data.Store',
    alias: ['coreModuleSettingsStore'],
    autoLoad: false,
    groupField: 'user',
    pageSize: 100,
    proxy: {
        type: 'gosDataProxyAjax',
        url: baseDir + 'core/module/setting'
    },
    model: 'GibsonOS.module.core.module.model.Setting'
});