Ext.define('GibsonOS.module.core.user.App', {
    extend: 'GibsonOS.App',
    alias: ['widget.gosModuleCoreUserApp'],
    id: 'coreUser',
    appIcon: 'icon_user',
    title: 'Benutzer',
    width: 600,
    height: 260,
    requiredPermission: {
        module: 'core',
        task: 'user'
    },
    enableToolbar: true,
    enableKeyEvents: true,
    enableClickEvents: false,
    enableContextMenu: true,
    addButton: {
        requiredPermission: {
            action: 'save',
            permission: GibsonOS.Permission.MANAGE + GibsonOS.Permission.WRITE
        }
    },
    addFunction() {
        const me = this;

        new GibsonOS.module.core.user.add.Window({
            gos: {
                data: {
                    success: function(form, action) {
                        const data = action.result.data;

                        me.down('#coreUserGrid').getStore().add({
                            id: data.id,
                            user: data.user
                        });

                        form.up('window').close();
                    }
                }
            }
        });
    },
    deleteButton: {
        requiredPermission: {
            action: 'delete',
            permission: GibsonOS.Permission.MANAGE + GibsonOS.Permission.DELETE
        }
    },
    deleteFunction(records) {
        const me = this;
        const grid = me.down('gosModuleCoreUserGrid');
        const record = records[0];

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
            }
        });
    },
    initComponent: function() {
        let me = this;

        me = GibsonOS.decorator.Panel.init(me);

        me.items = [{
            layout: 'border',
            items: [{
                xtype: 'gosModuleCoreUserGrid',
                region: 'west',
                width: 120,
                collapsible: true,
                split: true,
                flex: 0,
                hideCollapseTool: true,
                enableToolbar: false,
                enableKeyEvents: true,
                enableClickEvents: false,
                enableContextMenu: true,
                addButton: me.addButton,
                addFunction: me.addFunction,
                deleteButton: me.deleteButton,
                deleteFunction: me.deleteFunction
            },{
                xtype: 'gosPanel',
                itemId: 'coreUserView',
                region: 'center',
                layout: 'fit'
            }]
        }];

        me.callParent();

        me.viewItem = me.down('gosModuleCoreUserGrid');
        GibsonOS.decorator.Panel.addListeners(me);
    }
});