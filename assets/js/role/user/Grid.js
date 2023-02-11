Ext.define('GibsonOS.module.core.role.user.Grid', {
    extend: 'GibsonOS.module.core.component.grid.Panel',
    alias: ['widget.gosModuleCoreRoleUserGrid'],
    header: false,
    multiSelect: true,
    addFunction() {

    },
    deleteFunction(records) {
        const me = this;
        let ids = [];
        let msg = 'Möchten Sie den Benutzer ' + records[0].get('user') + ' wirklich löschen?';

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
        me.columns = [{
            header: 'Benutzer',
            dataIndex: 'user',
            flex: 1
        }];

        me.callParent();
    }
});