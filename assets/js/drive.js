Ext.define('coreDriveIndexGridModel', {
    extend: 'GibsonOS.data.Model',
    fields: [{
        name: 'id',
        type: 'int'
    },{
        name: 'serial',
        type: 'string'
    },{
        name: 'model',
        type: 'string'
    }]
});

function coreDriveIndex()
{
    var gridStore = new GibsonOS.data.Store({
        proxy: {
            type: 'gosDataProxyAjax',
            url: baseDir + 'core/drive',
            method: 'GET'
        },
        model: 'coreDriveIndexGridModel',
        listeners: {
            load: function(store, records, succesful) {
                var chartAxisFields = [];
                var chartSeries = [];
                var chartModelFields = [{
                    name: 'date',
                    type: 'string'
                },{
                    name: 'timestamp',
                    type: 'date',
                    dateFormat: 'timestamp'
                }];
                
                Ext.each(records, function(item) {
                    chartAxisFields.push(item.get('serial'));
                    chartModelFields.push({
                        name: item.get('serial'),
                        type: 'int'
                    });
                    chartSeries.push({
                        type: 'line',
                        highlight: {
                            size: 7,
                            radius: 7
                        },
                        axis: 'left',
                        smooth: true,
                        xField: 'date',
                        yField: item.get('serial'),
                        tips: {
                            trackMouse: true,
                            width: 150,
                            renderer: function(storeItem, valueItem) {
                                this.setTitle(item.get('serial') + '<br />' + storeItem.get('date') + ': ' + valueItem.value[1]);
                            }
                        }
                    });
                });
                
                Ext.define('coreDriveIndexChartModel', {
                    extend: 'GibsonOS.data.Model',
                    fields: chartModelFields
                });
                
                var chartStore = new GibsonOS.data.Store({
                    proxy: {
                        type: 'gosDataProxyAjax',
                        url: baseDir + 'core/drive/chart'
                    },
                    model: 'coreDriveIndexChartModel',
                    listeners: {
                        load            : function(store, records, successful, operation, opts) {
                            Ext.getCmp('core_drive_date_picker_from').picker.setValue(records[0].get('timestamp'));
                            Ext.getCmp('core_drive_picker_time_from').setValue(Ext.Date.format(records[0].get('timestamp'), 'H:i'));
                            Ext.getCmp('core_drive_date_from').setText(Ext.Date.format(records[0].get('timestamp'), 'd.m.Y'));
                            
                            Ext.getCmp('core_drive_date_picker_to').picker.setValue(records[records.length-1].get('timestamp'));
                            Ext.getCmp('core_drive_picker_time_to').setValue(Ext.Date.format(records[records.length-1].get('timestamp'), 'H:i'));
                            Ext.getCmp('core_drive_date_to').setText(Ext.Date.format(records[records.length-1].get('timestamp'), 'd.m.Y'));
                        }
                    }
                });
                
                new GibsonOS.App({
                    id: 'core_drive',
                    title: 'Festplatte',
                    appIcon: 'icon_harddrive',
                    width: 800,
                    height: 550,
                    items: [{
                        xtype: 'gosChartChart',
                        store: chartStore,
                        legend: {
                            position: 'left'
                        },
                        axes: [{
                            type: 'Numeric',
                            position: 'left',
                            minorTickSteps: 1,
                            fields: chartAxisFields,
                            grid: true
                        },{
                            type: 'Category',
                            position: 'bottom',
                            fields: ['date']
                        }],
                        series: chartSeries
                    }],
                    dockedItems: [{
                        xtype: 'gosToolbar',
                        dock: 'top',
                        items: [{
                            xtype: 'gosFormComboBox',
                            fieldLabel: 'S.M.A.R.T. Attribut',
                            labelWidth: 105,
                            width: 350,
                            value: 194,
                            store: {
                                xtype: 'gosDataStore',
                                fields: [{
                                    name: 'id',
                                    type: 'int'
                                },{
                                    name: 'name',
                                    type: 'string'
                                }],
                                data: store.getProxy().getReader().rawData.attributes
                            },
                            listeners: {
                                select: function(combo, records) {
                                    chartStore.getProxy().extraParams.attributeId = records[0].get('id');
                                    chartStore.load();
                                }
                            }
                        },('-'),{
                            id: 'core_drive_date_from',
                            text: 'Von',
                            menu: {
                                xtype: 'gosMenuDatePicker',
                                id: 'core_drive_date_picker_from',
                                handler: function(datepicker, date) {
                                    Ext.getCmp('core_drive_date_from').setText(Ext.Date.format(date, 'd.m.Y'));
                                }
                            }
                        },{
                            xtype: 'gosFormTimefield',
                            id: 'core_drive_picker_time_from',
                            hideLabel: true,
                            width: 60
                        },('-'),{
                            id: 'core_drive_date_to',
                            text: 'Bis',
                            menu: {
                                xtype: 'gosMenuDatePicker',
                                id: 'core_drive_date_picker_to',
                                handler: function(datepicker, date) {
                                    Ext.getCmp('core_drive_date_to').setText(Ext.Date.format(date, 'd.m.Y'));
                                }
                            }
                        },{
                            xtype: 'gosFormTimefield',
                            id: 'core_drive_picker_time_to',
                            hideLabel: true,
                            width: 60
                        },('-'),{
                            text: 'Setzen',
                            handler: function() {
                                var from = Ext.Date.format(Ext.getCmp('core_drive_date_picker_from').picker.getValue(), 'Y-m-d')
                                         + ' ' + Ext.Date.format(Ext.getCmp('core_drive_picker_time_from').getValue(), 'H:i');
                                chartStore.getProxy().extraParams.from = from;
                                
                                var to = Ext.Date.format(Ext.getCmp('core_drive_date_picker_to').picker.getValue(), 'Y-m-d')
                                         + ' ' + Ext.Date.format(Ext.getCmp('core_drive_picker_time_to').getValue(), 'H:i');
                                chartStore.getProxy().extraParams.to = to;
                                
                                chartStore.load();
                            }
                        }]
                    }]
                }).show();
            }
        }
    });
}