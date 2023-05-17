Ext.define('GibsonOS.module.core.icon.form.Panel', {
    extend: 'GibsonOS.form.Panel',
    alias: ['widget.gosModuleCoreIconFormPanel'],
    itemId: 'coreIconFormPanel',
    initComponent: function() {
        var formPanel = this;

        this.items = [{
            xtype: 'gosFormTextfield',
            fieldLabel: 'Name',
            name: 'name',
            value: this.gos.data.record ? this.gos.data.record.get('name') : null,
            requiredPermission: {
                action: '',
                method: 'POST',
                permission: GibsonOS.Permission.WRITE
            }
        },{
            xtype: 'gosFormTextfield',
            fieldLabel: 'Tags',
            name: 'tags',
            value: this.gos.data.record ? this.gos.data.record.get('tags') : null,
            requiredPermission: {
                action: '',
                method: 'POST',
                permission: GibsonOS.Permission.WRITE
            }
        },{
            xtype: this.gos.data.record ? 'gosFormTextfield' : 'gosFormFile',
            fieldLabel: 'Icon',
            name: 'icon',
            disabled: !!this.gos.data.record,
            requiredPermission: {
                action: '',
                method: 'POST',
                permission: GibsonOS.Permission.WRITE
            }
        },{
            xtype: this.gos.data.record ? 'gosFormTextfield' : 'gosFormFile',
            fieldLabel: 'Icon (*.ico)',
            name: 'iconIco',
            disabled: !!this.gos.data.record,
            requiredPermission: {
                action: '',
                method: 'POST',
                permission: GibsonOS.Permission.WRITE
            }
        }];
        this.buttons = [{
            xtype: 'gosButton',
            text: 'Speichern',
            requiredPermission: {
                action: '',
                method: 'POST',
                permission: GibsonOS.Permission.WRITE
            },
            handler: function() {
                formPanel.getForm().submit({
                    xtype: 'gosFormActionAction',
                    url: baseDir + 'core/icon',
                    method: 'POST',
                    params: {
                        id: formPanel.gos.data.record ? formPanel.gos.data.record.get('id') : null
                    },
                    success: function(form, action) {
                        var data = action.result.data;

                        if (formPanel.gos.data.record) {
                            formPanel.gos.data.record.set('name', data.name);
                            formPanel.gos.data.record.set('tags', data.tags);
                        } else {
                            GibsonOS.module.core.icon.fn.addStyle(data);
                        }
                    }
                });
            }
        }];

        this.callParent();
    }
});