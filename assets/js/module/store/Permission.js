Ext.define('GibsonOS.module.core.module.store.Permission', {
    extend: 'GibsonOS.data.Store',
    alias: ['coreModulePermissionStore'],
    autoLoad: false,
    pageSize: 100,
    proxy: {
        type: 'gosDataProxyAjax',
        url: baseDir + 'system/module/permission'
    },
    model: 'GibsonOS.module.core.module.model.Permission',
    constructor: function(data) {
        this.callParent(data);

        this.on('update', function(store, record, operation, options) {
            GibsonOS.Ajax.request({
                url: baseDir + 'core/user/savePermission',
                params: {
                    id: record.get('user_id'),
                    permission: record.get('permission'),
                    module: store.gos.data.module,
                    task: store.gos.data.task,
                    action: store.gos.data.action
                },
                failure: function() {
                    GibsonOS.MessageBox.show({msg: 'Berechtigung konnte nicht gespeichert werden!'});
                }
            });
        }, this, {
            priority: -999
        });

        return this;
    }
});