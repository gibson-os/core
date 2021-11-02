Ext.define('GibsonOS.module.system.user.device.Grid', {
    extend: 'GibsonOS.grid.Panel',
    alias: ['widget.gosModuleSystemUserDeviceGrid'],
    title: 'Geräte',
    multiSelect: true,
    itemId: 'systemUserDeviceGrid',
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

        this.store = new GibsonOS.module.system.user.store.Device();
        this.columns = [{
            header: 'Model',
            dataIndex: 'model',
            width: 200
        },{
            header: 'Registration ID',
            dataIndex: 'registration_id',
            flex: 1
        }];
        this.tbar = [{
            xtype: 'gosButton',
            iconCls: 'icon_system system_delete',
            itemId: 'systemUserDeviceDeleteButton',
            disabled: true,
            requiredPermission: {
                action: 'deletedevice',
                permission: permissionDelete
            },
            handler: function() {
                var button = this;
                var ids = [];
                var msg = 'Möchten Sie das Gerät wirklich löschen?';

                if (grid.getSelectionModel().getCount() > 1) {
                    msg = 'Möchten Sie die Geräte wirklich löschen?';
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
                    url: baseDir + 'system/user/deletedevice',
                    params: {
                        user: grid.gos.data.userId ? grid.gos.data.userId : 0,
                        'devices[]': ids
                    },
                    success: function(response, options) {
                        grid.getStore().remove(grid.getSelectionModel().getSelection());
                        button.disable();
                    },
                    failure: function(response, options) {
                        GibsonOS.MessageBox.show({msg: 'Geräte konnten nicht gelöscht werden!'});
                    }
                });
            }
        }];

        this.callParent();

        this.on('select', function(selection, record, index, options) {
            grid.down('#systemUserDeviceDeleteButton').enable();
        });
        this.on('deselect', function(selection, record, index, options) {
            if (selection.getCount() == 0) {
                grid.down('#systemUserDeviceDeleteButton').disable();
            }
        });
    }
});