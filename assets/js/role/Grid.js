Ext.define('GibsonOS.module.core.role.Grid', {
    extend: 'GibsonOS.module.core.component.grid.Panel',
    alias: ['widget.gosModuleCoreRoleGrid'],
    header: false,
    enableClickEvents: false,
    plugins: [{
        ptype: 'gosGridPluginCellEditing',
        clicksToEdit: 2
    }],
    addButton: {
        requiredPermission: {
            action: 'save',
            permission: GibsonOS.Permission.MANAGE + GibsonOS.Permission.WRITE
        }
    },
    addFunction() {
        const me = this;
        const record = me.getStore().add({})[0];

        me.plugins[0].startEdit(record, 0);
    },
    deleteButton: {
        requiredPermission: {
            action: 'delete',
            permission: GibsonOS.Permission.MANAGE + GibsonOS.Permission.DELETE
        }
    },
    deleteFunction(records) {
        const me = this;
        const record = records[0];

        GibsonOS.MessageBox.show({
            title: 'Wirklich löschen?',
            msg: 'Möchten Sie die Rolle ' + record.get('name') + ' wirklich löschen?',
            type: GibsonOS.MessageBox.type.QUESTION,
            buttons: [{
                text: 'Ja',
                sendRequest: true
            },{
                text: 'Nein'
            }]
        },{
            url: baseDir + 'core/role/delete',
            params: {
                id: record.get('id')
            },
            success() {
                me.getStore().load();
            }
        });
    },
    initComponent() {
        const me = this;

        me.store = new GibsonOS.module.core.role.store.Role();

        me.callParent();

        me.store.on('update', (store, record) => {
            GibsonOS.Ajax.request({
                url: baseDir + 'core/role/save',
                params: {
                    id: record.get('id'),
                    name: record.get('name')
                },
                success(response) {
                    const data = Ext.decode(response.responseText).data;

                    me.store.suspendEvent('update');
                    record.set('id', data.id);
                    record.commit();
                    me.store.resumeEvent('update');
                }
            });
        }, me.store, {
            priority: -999
        });
    },
    getColumns() {
        return [{
            header: 'Rolle',
            dataIndex: 'name',
            flex: 1,
            editor: {
                xtype: 'gosCoreComponentFormFieldTextField',
                hideLabel: true
            }
        }];
    }
});