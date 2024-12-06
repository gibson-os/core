Ext.define('GibsonOS.module.core.user.TabPanel', {
    extend: 'GibsonOS.TabPanel',
    alias: ['widget.gosModuleCoreUserTabPanel'],
    requiredPermission: {
        module: 'core',
        task: 'user'
    },
    initComponent: function() {
        const me = this;

        me.items = [{
            xtype: 'gosModuleCoreUserForm',
            gos: {
                data: this.gos.data
            }
        },{
            xtype: 'gosModuleCoreUserDeviceGrid',
            gos: {
                data: this.gos.data
            }
        }];

        me.callParent();

        me.on('render', function(panel) {
            let params = {};

            if (panel.gos.data.userId) {
                params.id = panel.gos.data.userId;
            }

            GibsonOS.Ajax.request({
                url: baseDir + 'core/user/settings',
                method: 'GET',
                params: params,
                success: function(response) {
                    const data = Ext.decode(response.responseText).data;

                    panel.down('#coreUserFormUsername').setValue(data.user);
                    panel.down('#coreUserFormHost').setValue(data.host);
                    panel.down('#coreUserFormIp').setValue(data.ip);

                    panel.down('#coreUserDeviceGrid').getStore().loadData(data.devices);
                },
                failure: function() {
                    GibsonOS.MessageBox.show({msg: 'Benutzerdaten konnten nicht geladen werden!'});
                }
            });
        });
    }
});