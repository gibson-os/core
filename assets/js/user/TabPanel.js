Ext.define('GibsonOS.module.system.user.TabPanel', {
    extend: 'GibsonOS.TabPanel',
    alias: ['widget.gosModuleSystemUserTabPanel'],
    requiredPermission: {
        module: 'system',
        task: 'user'
    },
    initComponent: function() {
        this.items = [{
            xtype: 'gosModuleSystemUserForm',
            gos: {
                data: this.gos.data
            }
        },{
            xtype: 'gosModuleSystemUserDeviceGrid',
            gos: {
                data: this.gos.data
            }
        },{
            xtype: 'gosModuleSystemUserHostGrid',
            gos: {
                data: this.gos.data
            }
        }];

        this.callParent();

        this.on('render', function(panel) {
            GibsonOS.Ajax.request({
                url: baseDir + 'core/user/settings',
                params: {
                    user: panel.gos.data.userId ? panel.gos.data.userId : 0
                },
                success: function(response) {
                    var data = Ext.decode(response.responseText).data;
                    var settings = data.settings;

                    panel.down('#systemUserFormUsername').setValue(settings.username);
                    panel.down('#systemUserFormHost').setValue(settings.host);
                    panel.down('#systemUserFormIp').setValue(settings.ip);

                    panel.down('#systemUserDeviceGrid').getStore().loadData(data.devices);
                    panel.down('#systemUserHostGrid').getStore().loadData(data.hosts);
                },
                failure: function() {
                    GibsonOS.MessageBox.show({msg: 'Benutzerdaten konnten nicht geladen werden!'});
                }
            });
        });
    }
});