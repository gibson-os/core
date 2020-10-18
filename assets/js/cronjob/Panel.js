Ext.define('GibsonOS.module.core.cronjob.Panel', {
    extend: 'GibsonOS.core.component.Panel',
    alias: ['widget.gosModuleCoreCronjobPanel'],
    layout: 'border',
    initComponent: function () {
        let me = this;

        me.items = [{
            xtype: 'gosModuleCoreCronjobForm',
            region: 'north',
            flex: 0,
            autoHeight: true
        },{
            xtype: 'gosModuleCoreCronjobTimeGrid',
            region: 'center'
        }];

        me.addButton = {
            iconCls: 'icon_system system_save'
        };

        me.callParent();
    },
    addFunction: function() {
        let me = this;
        let form = me.down('gosModuleCoreEventForm').getForm();

        let times = [];

        me.down('gosModuleCoreCronjobTimeGrid').getStore().each(function(time) {
            times.push(time.getData());
        });

        me.setLoading(true);

        form.submit({
            url: baseDir + 'core/cronjob/save',
            params: {
                times: Ext.encode(times)
            },
            callback: function() {
                me.setLoading(false);
            },
            success: function(response) {
                form.findField('id').setValue(Ext.decode(response.responseText).data.id);
            }
        });
    },
    deleteFunction: function(records) {

    }
});