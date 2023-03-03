Ext.define('GibsonOS.module.core.role.user.Grid', {
    extend: 'GibsonOS.module.core.component.grid.Panel',
    alias: ['widget.gosModuleCoreRoleUserGrid'],
    header: false,
    multiSelect: true,
    plugins: [{
        ptype: 'gosGridPluginCellEditing',
        clicksToEdit: 2
    }],
    addFunction() {
        const me = this;
        const record = me.getStore().add({})[0];

        me.plugins[0].startEdit(record, 0);
    },
    deleteFunction(records) {
        const me = this;
        let ids = [];
        let msg = 'Möchten Sie den Benutzer ' + records[0].get('userName') + ' wirklich löschen?';

        if (records.length > 1) {
            msg = 'Möchten Sie die ' + records.length + ' Benutzer wirklich löschen?';
        }

        Ext.iterate(records, (record) => {
            ids.push({id: record.getId()});
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
            url: baseDir + 'core/role/deleteUsers',
            params: {
                users: Ext.encode(ids)
            },
            success() {
                me.getStore().load();
            }
        });
    },
    initComponent() {
        const me = this;

        me.store = new GibsonOS.module.core.role.store.User();

        me.callParent();

        me.store.on('update', (store, record) => {
            GibsonOS.Ajax.request({
                url: baseDir + 'core/role/saveUser',
                params: {
                    id: record.get('id'),
                    roleId: me.store.getProxy().extraParams.id,
                    userId: record.get('userId')
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
        const me = this;

        return [{
            header: 'Benutzer',
            dataIndex: 'userName',
            flex: 1,
            editor: {
                xtype: 'gosModuleCoreParameterTypeAutoComplete',
                hideLabel: true,
                emptyText: 'Benutzer',
                parameterObject: {
                    config: {
                        model: 'GibsonOS.module.core.user.model.User',
                        autoCompleteClassname: 'GibsonOS\\Core\\AutoComplete\\UserAutoComplete',
                        displayField: 'user'
                    }
                },
                listeners: {
                    select(combo, records) {
                        me.getSelectionModel().getSelection()[0].set('userId', records[0].get('id'));
                    }
                }
            }
        }];
    }
});