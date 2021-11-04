Ext.define('GibsonOS.module.core.module.setting.Grid', {
    extend: 'GibsonOS.grid.Panel',
    alias: ['widget.gosModuleCoreModuleSettingGrid'],
    itemId: 'coreModuleManageSettingsGrid',
    features: [{
        ftype: 'gosGridFeatureGrouping'
    }],
    initComponent: function() {
        this.store = new GibsonOS.module.core.module.store.Permission();
        this.columns = [{
            header: 'Schlüssel',
            dataIndex: 'key',
            flex: 1
        },{
            header: 'Wert',
            dataIndex: 'value',
            flex: 1
        }]

        this.callParent();
    }
});