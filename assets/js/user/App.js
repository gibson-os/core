Ext.define('GibsonOS.module.system.user.App', {
    extend: 'GibsonOS.App',
    alias: ['widget.gosModuleSystemUserApp'],
    id: 'systemUser',
    appIcon: 'icon_user',
    title: 'Benutzer',
    width: 600,
    height: 260,
    requiredPermission: {
        module: 'system',
        task: 'user'
    },
    initComponent: function() {
        var app = this;
        this.tbar = [{
            xtype: 'gosButton',
            iconCls: 'icon_system system_add',
            requiredPermission: {
                action: 'save',
                permission: GibsonOS.Permission.WRITE
            },
            handler: function() {
                new GibsonOS.module.system.user.add.Window({
                    gos: {
                        data: {
                            success: function(form, action) {
                                var data = action.result.data;

                                app.down('#systemUserGrid').getStore().add({
                                    id: data.id,
                                    user: data.user
                                });

                                form.up('window').close();
                            }
                        }
                    }
                });
            }
        },{
            xtype: 'gosButton',
            itemId: 'systemUserDeleteButton',
            iconCls: 'icon_system system_delete',
            requiredPermission: {
                action: 'delete',
                permission: GibsonOS.Permission.DELETE
            },
            disabled: true,
            handler: function() {
                var button = this;
                var grid = app.down('#systemUserGrid');
                var record = grid.getSelectionModel().getSelection()[0];

                GibsonOS.MessageBox.show({
                    title: 'Wirklich löschen?',
                    msg: 'Möchten Sie den Benutzer ' + record.get('user') + ' wirklich löschen?',
                    type: GibsonOS.MessageBox.type.QUESTION,
                    buttons: [{
                        text: 'Ja',
                        sendRequest: true
                    },{
                        text: 'Nein'
                    }]
                },{
                    url: baseDir + 'core/user/delete',
                    params: {
                        id: record.get('id')
                    },
                    success: function(response) {
                        grid.getStore().remove(grid.getSelectionModel().getSelection());
                        button.disable();
                    }
                });
            }
        }];
        this.items = [{
            layout: 'border',
            items: [{
                xtype: 'gosModuleSystemUserGrid',
                region: 'west',
                width: 120,
                collapsible: true,
                split: true,
                flex: 0,
                hideCollapseTool: true
            },{
                xtype: 'gosPanel',
                itemId: 'systemUserView',
                region: 'center',
                layout: 'fit'
            }]
        }];

        this.callParent();

        this.down('#systemUserGrid').on('select', function(selection, record, index, options) {
            app.down('#systemUserDeleteButton').enable();
        });
        this.down('#systemUserGrid').on('deselect', function(selection, record, index, options) {
            if (selection.getCount() == 0) {
                app.down('#systemUserDeleteButton').disable();
            }
        });
    }
});