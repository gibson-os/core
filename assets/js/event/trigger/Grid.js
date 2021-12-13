Ext.define('GibsonOS.module.core.event.trigger.Grid', {
    extend: 'GibsonOS.module.core.component.grid.Panel',
    alias: ['widget.gosModuleCoreEventTriggerGrid'],
    autoScroll: true,
    initComponent: function() {
        let me = this;

        me.store = new GibsonOS.module.core.event.trigger.store.Grid();

        me.plugins = [
            Ext.create('Ext.grid.plugin.RowEditing', {
                saveBtnText: 'Speichern',
                cancelBtnText: 'Abbrechen',
                clicksToMoveEditor: 1,
                pluginId: 'rowEditing',
                listeners: {
                    beforeedit: function(editor, context) {
                        let form = editor.getEditor().getForm();
                        let record = context.record;
                        let triggerComboBox = form.findField('trigger');
                        let parametersCheckbox = form.findField('hasParameters');

                        triggerComboBox.disable();
                        parametersCheckbox.disable();

                        if (!record.get('className')) {
                            return;
                        }

                        let triggerComboBoxStore = triggerComboBox.getStore();

                        triggerComboBoxStore.getProxy().setExtraParam('className', record.get('className'));
                        triggerComboBoxStore.load(function(records) {
                            if (!record.get('trigger')) {
                                return;
                            }

                            let triggerRecord = null;

                            Ext.iterate(records, function(iterateRecord) {
                                if (iterateRecord.get('trigger') === record.get('trigger')) {
                                    triggerRecord = iterateRecord;
                                    return false;
                                }
                            });

                            if (!Ext.Object.isEmpty(triggerRecord.get('parameters'))) {
                                Ext.iterate(triggerRecord.get('parameters'), function(name, parameter) {
                                    parameter.value = record.get('parameters')[name].value;
                                    parameter.operator = record.get('parameters')[name].operator;
                                });

                                parametersCheckbox.enable();
                            }
                        });
                        triggerComboBox.enable();
                    }
                }
            })
        ];

        me.callParent();
    },
    addFunction: function() {
        let me = this;
        me.plugins[0].startEdit(me.getStore().add({})[0], 1);
    },
    deleteFunction: function(records) {
        let me = this;
        me.getStore().remove(records);
    },
    getColumns: function() {
        let me = this;

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
                        let triggerColumnEditor = me.down('#gosModuleCoreEventTriggerGridColumnTrigger').getEditor();
                        let triggerColumnEditorStore = triggerColumnEditor.getStore();

                        triggerColumnEditor.setValue(null);

                        triggerColumnEditorStore.getProxy().setExtraParam('className', newValue);
                        triggerColumnEditorStore.load();

                        triggerColumnEditor.enable();
                    }
                }
            },
            renderer: function(value, metaData, record) {
                let column = me.down('#gosModuleCoreEventTriggerGridColumnClassName');
                let comboBox = column.getEditor();
                let comboRecord = comboBox.findRecordByValue(value);

                return comboBox.getStore().count() === 0 ? record.get('classNameTitle') :
                    comboRecord === false ? null : comboRecord.get('title');
            }
        },{
            header: 'Trigger',
            dataIndex: 'trigger',
            itemId: 'gosModuleCoreEventTriggerGridColumnTrigger',
            flex: 1,
            editor: {
                xtype: 'gosModuleCoreEventElementTriggerComboBox',
                listeners: {
                    change: function(comboBox, newValue) {
                        let record = comboBox.findRecordByValue(newValue);
                        let parametersCheckbox = me.down('#gosModuleCoreEventTriggerGridColumnParameters').getEditor();
                        let parameters = null;

                        parametersCheckbox.disable();

                        if (record) {
                            parameters = record.get('parameters');

                            if (!Ext.Object.isEmpty(parameters)) {
                                parametersCheckbox.enable();
                            }
                        }
                    }
                }
            },
            renderer: function(value, metaData, record) {
                let column = me.down('#gosModuleCoreEventTriggerGridColumnTrigger');
                let comboBox = column.getEditor();
                let comboRecord = comboBox.findRecordByValue(value);

                return comboBox.getStore().count() === 0 ? record.get('triggerTitle') :
                    comboRecord === false ? null : comboRecord.get('title');
            }
        },{
            header: 'Parameter',
            itemId: 'gosModuleCoreEventTriggerGridColumnParameters',
            dataIndex: 'hasParameters',
            flex: 1,
            renderer: function(value, metaData, record) {
                let values = record.get('parameters');
                let triggerComboBox = me.down('#gosModuleCoreEventTriggerGridColumnTrigger').getEditor();
                let triggerComboBoxRecord = triggerComboBox.findRecordByValue(triggerComboBox.getValue());
                let returnValue = '';
                let parameters = record.get('parameters');

                if (triggerComboBoxRecord) {
                    parameters = triggerComboBoxRecord.get('parameters');
                }

                if (!parameters) {
                    return returnValue;
                }

                Ext.iterate(parameters, function(name, parameter) {
                    if (!values[name] || !values[name].value) {
                        return true;
                    }

                    returnValue +=
                        parameter.title + ' ' +
                        (values[name].operator ? values[name].operator + ' ' : ' ') +
                        (values[name].value ? values[name].value : '') +
                        '<br>'
                    ;
                });

                return returnValue;
            },
            editor: {
                xtype: 'gosFormCheckbox',
                boxLabel: 'Bearbeiten',
                listeners: {
                    change: function(checkbox) {
                        let triggerComboBox = me.down('#gosModuleCoreEventTriggerGridColumnTrigger').getEditor();
                        let triggerComboBoxRecord = triggerComboBox.findRecordByValue(triggerComboBox.getValue());
                        let record = me.getSelectionModel().getSelection()[0];
                        record.set('parameters', triggerComboBoxRecord.get('parameters'));

                        checkbox.suspendEvents();
                        checkbox.setValue(true);
                        checkbox.resumeEvents();

                        new GibsonOS.module.core.parameter.Window({withOperator: true})
                            .down('gosModuleCoreParameterForm').addFields(record.get('parameters'))
                        ;
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