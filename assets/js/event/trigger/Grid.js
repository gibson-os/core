Ext.define('GibsonOS.module.core.event.trigger.Grid', {
    extend: 'GibsonOS.core.component.grid.Panel',
    alias: ['widget.gosModuleCoreEventTriggerGrid'],
    autoScroll: true,
    initComponent: function() {
        let me = this;

        me.store = new GibsonOS.module.core.event.trigger.store.Grid();
        me.store.getProxy().setExtraParam('eventId', me.gos.data.eventId);
        me.store.load();

        me.plugins = [
            Ext.create('Ext.grid.plugin.RowEditing', {
                saveBtnText: 'Speichern',
                cancelBtnText: 'Abbrechen',
                clicksToMoveEditor: 1,
                pluginId: 'rowEditing'
            })
        ];

        me.callParent();
    },
    addFunction: function() {
        let me = this;
        me.plugins[0].startEdit(me.getStore().add({})[0], 1);
    },
    deleteFunction: function() {
        let me = this;
        me.getStore().remove(me.getSelectionModel().getSelection());
    },
    getColumns: function() {
        return [{
            xtype: 'gosGridColumnComboBoxEditor',
            itemId: 'gosModuleCoreEventTriggerGridColumnClassName',
            header: 'Klasse',
            dataIndex: 'className',
            flex: 1,
            editor: {
                xtype: 'gosModuleCoreEventElementClassNameComboBox',
                listeners: {
                    change: function(comboBox, newValue) {
                        let methodColumnEditor = me.down('#gosModuleCoreEventElementTreeGridColumnMethod').getEditor();
                        let methodColumnEditorStore = methodColumnEditor.getStore();

                        methodColumnEditor.setValue(null);

                        methodColumnEditorStore.getProxy().setExtraParam('describerClass', newValue);
                        methodColumnEditorStore.load();

                        methodColumnEditor.enable();
                    }
                }
            },
            renderer: function(value, metaData, record) {
                // let column = me.down('#gosModuleCoreEventElementTreeGridColumnClassName');
                // let comboBox = column.getEditor();
                // let comboRecord = comboBox.findRecordByValue(value);
                //
                // return comboBox.getStore().count() === 0 ? record.get('classNameTitle') :
                //     comboRecord === false ? null : comboRecord.get('title');
            }
        },{
            header: 'Trigger',
            dataIndex: 'trigger',
            flex: 1,
            editor: {
                xtype: 'gosFormTextfield',
                hideLabel: true
            }
        },{
            header: 'Parameter',
            itemId: 'gosModuleCoreEventTriggerGridColumnParameters',
            dataIndex: 'hasParameters',
            flex: 1,
            renderer: function(value, metaData, record) {
                // let values = record.get('parameters');
                // let methodComboBox = me.down('#gosModuleCoreEventElementTreeGridColumnMethod').getEditor();
                // let methodComboBoxRecord = methodComboBox.findRecordByValue(methodComboBox.getValue());
                // let returnValue = '';
                // let parameters = record.get('parameters');
                //
                // if (methodComboBoxRecord) {
                //     parameters = methodComboBoxRecord.get('parameters');
                // }
                //
                // if (!parameters) {
                //     return returnValue;
                // }
                //
                // Ext.iterate(parameters, function(name, parameter) {
                //     returnValue += parameter.title + ': ';
                //
                //     if (values[name]) {
                //         returnValue += values[name].value ? values[name].value : '';
                //     }
                //
                //     returnValue += '<br>';
                // });
                //
                // return returnValue;
            },
            editor: {
                xtype: 'gosFormCheckbox',
                boxLabel: 'Bearbeiten',
                listeners: {
                    change: function(checkbox) {
                        let methodComboBox = me.down('#gosModuleCoreEventElementTreeGridColumnMethod').getEditor();
                        let methodComboBoxRecord = methodComboBox.findRecordByValue(methodComboBox.getValue());
                        let record = me.getSelectionModel().getSelection()[0];
                        record.set('parameters', methodComboBoxRecord.get('parameters'));

                        checkbox.suspendEvents();
                        checkbox.setValue(true);
                        checkbox.resumeEvents();

                        new GibsonOS.module.core.event.element.parameter.Window({
                            gos: {
                                data: record.get('parameters')
                            }
                        });
                    }
                }
            }
        },{
            header: 'Wochentag',
            dataIndex: 'weekday',
            width: 70,
            editor: {
                xtype: 'gosFormComboBox',
                emptyText: 'Keiner',
                hideLabel: true,
                store: {
                    xtype: 'gosDataStore',
                    fields: ['id', 'name'],
                    data: [{
                        id: null,
                        name: 'Keiner'
                    },{
                        id: 1,
                        name: 'Montag'
                    },{
                        id: 2,
                        name: 'Dienstag'
                    },{
                        id: 3,
                        name: 'Mittwoch'
                    },{
                        id: 4,
                        name: 'Donnerstag'
                    },{
                        id: 5,
                        name: 'Freitag'
                    },{
                        id: 6,
                        name: 'Samstag'
                    },{
                        id: 0,
                        name: 'Sonntag'
                    },{
                        id: 1,
                        name: 'Montag'
                    }]
                }
            }
        },{
            header: 'Tag',
            dataIndex: 'day',
            width: 50,
            editor: {
                xtype: 'gosFormNumberfield',
                minValue: 1,
                maxValue: 31,
                hideLabel: true
            }
        },{
            header: 'Monat',
            dataIndex: 'month',
            width: 50,
            editor: {
                xtype: 'gosFormNumberfield',
                minValue: 1,
                maxValue: 12,
                hideLabel: true
            }
        },{
            header: 'Jahr',
            dataIndex: 'year',
            width: 50,
            editor: {
                xtype: 'gosFormNumberfield',
                minValue: 0,
                maxValue: 9999,
                hideLabel: true
            }
        },{
            header: 'Stunde',
            dataIndex: 'hour',
            width: 50,
            editor: {
                xtype: 'gosFormNumberfield',
                minValue: 0,
                maxValue: 23,
                hideLabel: true
            }
        },{
            header: 'Minute',
            dataIndex: 'minute',
            width: 50,
            editor: {
                xtype: 'gosFormNumberfield',
                minValue: 0,
                maxValue: 59,
                hideLabel: true
            }
        },{
            header: 'Sekunde',
            dataIndex: 'second',
            width: 55,
            editor: {
                xtype: 'gosFormNumberfield',
                minValue: 0,
                maxValue: 59,
                hideLabel: true
            }
        }];
    }
});