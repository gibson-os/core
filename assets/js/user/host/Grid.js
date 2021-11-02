Ext.define('GibsonOS.module.system.user.host.Grid', {
    extend: 'GibsonOS.grid.Panel',
    alias: ['widget.gosModuleSystemUserHostGrid'],
    title: 'Automatischer Login',
    multiSelect: true,
    itemId: 'systemUserHostGrid',
    initComponent: function() {
        var grid = this;
        var permissionWrite = GibsonOS.Permission.WRITE;
        var permissionDelete = GibsonOS.Permission.DELETE;

        if (
            this.gos.data.userId ||
            this.gos.data.add
        ) {
            permissionWrite += GibsonOS.Permission.MANAGE;
            permissionDelete += GibsonOS.Permission.MANAGE;
        }

        this.store = new GibsonOS.module.system.user.store.Host();
        this.columns = [{
            header: 'Host',
            dataIndex: 'host',
            flex: 1
        },{
            header: 'IP',
            dataIndex: 'ip',
            flex: 1
        }];
        this.tbar = [{
            xtype: 'gosButton',
            iconCls: 'icon_system system_add',
            requiredPermission: {
                action: 'savedevice',
                permission: GibsonOS.Permission.WRITE + GibsonOS.Permission.MANAGE
            },
            handler: function() {
                var gosData = grid.gos.data;
                gosData.success = function(form, action) {
                    grid.getStore().add(action.result.data);
                    form.up('window').close();
                };

                new GibsonOS.module.system.user.host.Window({
                    gos: {
                        data: gosData
                    }
                });
            }
        },{
            xtype: 'gosButton',
            iconCls: 'icon_system system_delete',
            itemId: 'systemUserHostDeleteButton',
            disabled: true,
            requiredPermission: {
                action: 'deletehost',
                permission: GibsonOS.Permission.WRITE + GibsonOS.Permission.DELETE
            },
            handler: function() {
                var button = this;
                var ids = [];
                var msg = 'Möchten Sie den Automatischen Login wirklich löschen?';

                if (grid.getSelectionModel().getCount() > 1) {
                    msg = 'Möchten Sie die Automatischen Logins wirklich löschen?';
                }

                Ext.iterate(grid.getSelectionModel().getSelection(), function(record) {
                    ids.push(record.get('id'));
                });

                GibsonOS.MessageBox.show({
                    title: 'Wirklich löschen?',
                    msg: msg,
                    type: GibsonOS.MessageBox.type.QUESTION,
                    buttons: [{
                        text: 'Ja',
                        sendRequest: true
                    },{
                        text: 'Nein'
                    }]
                },{
                    url: baseDir + 'system/user/deletehost',
                    params: {
                        user: grid.gos.data.userId ? grid.gos.data.userId : 0,
                        'hosts[]': ids
                    },
                    success: function() {
                        grid.getStore().remove(grid.getSelectionModel().getSelection());
                        button.disable();
                    },
                    failure: function() {
                        GibsonOS.MessageBox.show({msg: 'Hosts konnten nicht gelöscht werden!'});
                    }
                });
            }
        }];

        this.callParent();

        this.on('select', function(selection, record, index, options) {
            grid.down('#systemUserHostDeleteButton').enable();
        });
        this.on('deselect', function(selection, record, index, options) {
            if (selection.getCount() == 0) {
                grid.down('#systemUserHostDeleteButton').disable();
            }
        });
    }
});