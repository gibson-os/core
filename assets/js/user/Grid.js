Ext.define('GibsonOS.module.core.user.Grid', {
    extend: 'GibsonOS.module.core.component.grid.Panel',
    alias: ['widget.gosModuleCoreUserGrid'],
    itemId: 'coreUserGrid',
    header: false,
    hideHeaders: true,
    enablePagingBar: false,
    initComponent: function() {
        const me = this;

        me.store = new GibsonOS.module.core.user.store.User();
        me.columns = [{
            header: 'Benutzer',
            dataIndex: 'user',
            flex: 1
        }];

        me.callParent();

        me.on('select', function(selection, record) {
            const view = me.up('#app').down('#coreUserView');
            view.removeAll();
            view.add({
                xtype: 'gosModuleCoreUserTabPanel',
                gos: {
                    data: {
                        userId: record.get('id'),
                        success: function(form, action) {
                            const data = action.result.data;

                            record.set('user', data.user);
                            record.commit();
                        }
                    }
                }
            });
        });
    }
});