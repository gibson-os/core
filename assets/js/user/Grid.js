Ext.define('GibsonOS.module.system.user.Grid', {
    extend: 'GibsonOS.grid.Panel',
    alias: ['widget.gosModuleSystemUserGrid'],
    itemId: 'systemUserGrid',
    header: false,
    hideHeaders: true,
    initComponent: function() {
        var grid = this;

        this.store = new GibsonOS.module.system.user.store.User();
        this.columns = [{
            header: 'Benutzer',
            dataIndex: 'user',
            flex: 1
        }];

        this.callParent();

        this.on('select', function(selection, record, index, options) {
            var view = grid.up('#app').down('#systemUserView');
            view.removeAll();
            view.add({
                xtype: 'gosModuleSystemUserTabPanel',
                gos: {
                    data: {
                        userId: record.get('id'),
                        success: function(form, action) {
                            var data = action.result.data;

                            record.set('user', data.user);
                            record.commit();
                        }
                    }
                }
            });
        });
    }
});