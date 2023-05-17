Ext.define('GibsonOS.module.core.icon.Panel', {
    extend: 'GibsonOS.Panel',
    alias: ['widget.gosModuleCoreIconPanel'],
    itemId: 'coreIconPanel',
    layout: 'border',
    initComponent: function() {
        var panel = this;

        this.items = [{
            xtype: 'gosModuleCoreIconTagGrid',
            region: 'west',
            flex: 0,
            collapsible: true,
            split: true,
            width: 150,
            hideCollapseTool: true,
            header: false,
            hideHeaders: true
        },{
            xtype: 'gosModuleCoreIconView',
            region: 'center'
        }];
        this.tbar = [{
            xtype: 'gosButton',
            iconCls: 'icon_system system_add',
            requiredPermission: {
                action: '',
                method: 'POST',
                permission: GibsonOS.Permission.WRITE
            },
            handler: function() {
                var form = new GibsonOS.module.core.icon.form.Window();
                GibsonOS.module.core.icon.fn.actionComplete(form, panel);
            }
        },{
            xtype: 'gosButton',
            itemId: 'coreIconEditButton',
            iconCls: 'icon_system system_edit',
            disabled: true,
            requiredPermission: {
                action: '',
                method: 'POST',
                permission: GibsonOS.Permission.WRITE
            },
            handler: function() {
                var record = panel.down('#coreIconView').getSelectionModel().getSelection()[0];
                var form = new GibsonOS.module.core.icon.form.Window({
                    gos: {
                        data: {
                            record: record
                        }
                    }
                });
                GibsonOS.module.core.icon.fn.actionComplete(form, panel, record);
            }
        },{
            xtype: 'gosButton',
            itemId: 'coreIconDeleteButton',
            iconCls: 'icon_system system_delete',
            requiredPermission: {
                action: '',
                method: 'DELETE',
                permission: GibsonOS.Permission.DELETE
            },
            disabled: true,
            handler: function() {
                var view = panel.down('#coreIconView');
                var records = view.getSelectionModel().getSelection();

                GibsonOS.module.core.icon.fn.delete(records, function (response) {
                    var app = panel.up('#app');
                    view.getStore().remove(records);
                    panel.down('#coreIconTagGrid').getStore().loadData(Ext.decode(response.responseText).data);
                    panel.down('#coreIconEditButton').disable(true);
                    panel.down('#coreIconDeleteButton').disable(true);
                });
            }
        }];

        this.callParent();
// slectionchange!?
        this.down('#coreIconTagGrid').on('select', function(selection, record, options) {
            var tags = [];

            Ext.iterate(selection.getSelection(), function(record) {
                if (!record.get('tag')) {
                    return;
                }

                tags.push(record.get('tag'));
            });

            var store = panel.down('#coreIconView').getStore();
            store.getProxy().extraParams.tags = Ext.encode(tags);
            store.load();
        });
        this.down('#coreIconTagGrid').on('deselect', function(selection, record, options) {
            if (selection.getCount() == 0) {
                panel.down('#coreIconEditButton').disable();
                panel.down('#coreIconDeleteButton').disable();
            }
        });

        var view = this.down('#coreIconView');

        view.getStore().on('load', function(store, records, successful, options) {
            var grid = panel.down('#coreIconTagGrid');
            //grid.getStore().gos.functions.saveSelection();
            grid.getStore().loadData(store.getProxy().getReader().jsonData.tags);
            //grid.getStore().gos.functions.restoreSelection();
        });
        view.getSelectionModel().on('selectionchange', function(sm, records, options) {
            var editButton = panel.down('#coreIconEditButton');
            var deleteButton = panel.down('#coreIconDeleteButton');

            if (records.length) {
                if (records.length == 1) {
                    editButton.enable();
                } else {
                    editButton.disable();
                }

                deleteButton.enable();
            } else {
                editButton.disable();
                deleteButton.disable();
            }
        });
        view.on('itemdblclick', function(view, record, item, index, event, options) {
            var form = new GibsonOS.module.core.icon.form.Window({
                gos: {
                    data: {
                        record: record
                    }
                }
            });
            GibsonOS.module.core.icon.fn.actionComplete(form, panel, record);
        });
    }
});