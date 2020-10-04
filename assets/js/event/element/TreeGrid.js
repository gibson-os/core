Ext.define('GibsonOS.module.core.event.element.TreeGrid', {
    extend: 'GibsonOS.tree.Panel',
    alias: ['widget.gosModuleCoreEventElementTreeGrid'],
    autoScroll: true,
    useArrows: true,
    multiSelect: true,
    requiredPermission: {
        module: 'core',
        task: 'event'
    },
    initComponent: function () {
        let me = this;

        me.store = new GibsonOS.module.core.event.element.store.TreeGrid();
        me.store.getProxy().setExtraParam('eventId', me.gos.data.eventId);
        me.store.load();

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
                        let methodComboBox = form.findField('method');
                        let parametersCheckbox = form.findField('hasParameters');
                        let operatorComboBox = form.findField('operator');
                        let returnCheckbox = form.findField('hasReturn');

                        methodComboBox.disable();
                        parametersCheckbox.disable();
                        operatorComboBox.disable();
                        returnCheckbox.disable();

                        if (!record.get('className')) {
                            return;
                        }

                        let methodComboBoxStore = methodComboBox.getStore();

                        methodComboBoxStore.getProxy().setExtraParam('describerClass', record.get('className'));
                        methodComboBoxStore.load(function(records) {
                            if (!record.get('method')) {
                                return;
                            }

                            let methodRecord = null;

                            Ext.iterate(records, function(iterateRecord) {
                                if (iterateRecord.get('method') === record.get('method')) {
                                    methodRecord = iterateRecord;
                                    return false;
                                }
                            });

                            if (methodRecord.get('parameters')) {
                                if (record.get('parameters')) {
                                    Ext.iterate(methodRecord.get('parameters'), function(name, parameter) {
                                        parameter.value = record.get('parameters')[name];
                                    });
                                }

                                parametersCheckbox.enable();
                            }

                            if (methodRecord.get('returns')) {
                                if (record.get('returns')) {
                                    Ext.iterate(methodRecord.get('returns'), function(name, parameter) {
                                        parameter.value = record.get('returns')[name];
                                    });
                                }

                                operatorComboBox.enable();
                                returnCheckbox.enable();
                            }
                        });
                        methodComboBox.enable();
                    }
                }
            })
        ];
        me.columns = [{
            xtype: 'treecolumn'
        },{
            xtype: 'gosGridColumnComboBoxEditor',
            header: 'Kommando',
            dataIndex: 'command',
            width: 120,
            editor: {
                xtype: 'gosModuleCoreEventElementCommandComboBox',
                listeners: {
                    change: function(combo, newValue) {
                        let node = me.getSelectionModel().getSelection()[0];

                        if (newValue) {
                            node.set('leaf', false);
                        } else {
                            node.set('leaf', true);
                            node.removeAll();
                        }
                    }
                }
            }
        },{
            xtype: 'gosGridColumnComboBoxEditor',
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
            }
        },{
            xtype: 'gosGridColumnComboBoxEditor',
            header: 'Methode',
            itemId: 'gosModuleCoreEventElementTreeGridColumnMethod',
            dataIndex: 'method',
            flex: 1,
            editor: {
                xtype: 'gosModuleCoreEventElementMethodComboBox',
                listeners: {
                    change: function(comboBox, newValue) {
                        let record = comboBox.findRecordByValue(newValue);
                        let parametersCheckbox = me.down('#gosModuleCoreEventElementTreeGridColumnParameters').getEditor();
                        let returnCheckbox = me.down('#gosModuleCoreEventElementTreeGridColumnReturn').getEditor();
                        let operatorComboBox = me.down('#gosModuleCoreEventElementTreeGridColumnOperator').getEditor();
                        let parameters = null;
                        let returns = null;

                        parametersCheckbox.disable();
                        returnCheckbox.disable();
                        operatorComboBox.disable();
                        operatorComboBox.setValue(null);

                        if (record) {
                            parameters = record.get('parameters');
                            returns = record.get('returns');

                            if (parameters) {
                                parametersCheckbox.enable();
                            }

                            if (returns) {
                                operatorComboBox.enable();
                                returnCheckbox.enable();
                            }
                        }
                    }
                }
            }
        },{
            header: 'Parameter',
            itemId: 'gosModuleCoreEventElementTreeGridColumnParameters',
            dataIndex: 'hasParameters',
            flex: 1,
            renderer: function(value, metaData, record) {
                let values = record.get('parameters');
                let methodComboBox = me.down('#gosModuleCoreEventElementTreeGridColumnMethod').getEditor();
                let methodComboBoxRecord = methodComboBox.findRecordByValue(methodComboBox.getValue());
                let returnValue = '';

                if (
                    !methodComboBoxRecord ||
                    !methodComboBoxRecord.get('parameters')
                ) {
                    return returnValue;
                }

                Ext.iterate(methodComboBoxRecord.get('parameters'), function(name, parameter) {
                    returnValue += parameter.title + ': ';

                    if (values[name]) {
                        returnValue += values[name].value ? values[name].value : '';
                    }

                    returnValue += '<br>';
                });

                return returnValue;
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
            xtype: 'gosGridColumnComboBoxEditor',
            header: 'Operator',
            itemId: 'gosModuleCoreEventElementTreeGridColumnOperator',
            dataIndex: 'operator',
            width: 100,
            editor: {
                xtype: 'gosModuleCoreEventElementOperatorComboBox'
            }
        },{
            header: 'Wert',
            itemId: 'gosModuleCoreEventElementTreeGridColumnReturn',
            dataIndex: 'hasReturn',
            flex: 1,
            renderer: function(value, metaData, record) {
                let values = record.get('returns');
                let methodComboBox = me.down('#gosModuleCoreEventElementTreeGridColumnMethod').getEditor();
                let methodComboBoxRecord = methodComboBox.findRecordByValue(methodComboBox.getValue());
                let returnValue = '';

                if (
                    !record.get('operator') ||
                    !methodComboBoxRecord ||
                    !methodComboBoxRecord.get('returns')
                ) {
                    return returnValue;
                }

                Ext.iterate(methodComboBoxRecord.get('returns'), function(name, parameter) {
                    returnValue += parameter.title + ': ';

                    if (values[name]) {
                        returnValue += values[name].value ? values[name].value : '';
                    }

                    returnValue += '<br>';
                });

                return returnValue;
            },
            editor: {
                xtype: 'gosFormCheckbox',
                boxLabel: 'Bearbeiten',
                listeners: {
                    change: function(checkbox) {
                        let methodComboBox = me.down('#gosModuleCoreEventElementTreeGridColumnMethod').getEditor();
                        let methodComboBoxRecord = methodComboBox.findRecordByValue(methodComboBox.getValue());
                        let record = me.getSelectionModel().getSelection()[0];
                        record.set('returns', methodComboBoxRecord.get('returns'));

                        checkbox.suspendEvents();
                        checkbox.setValue(true);
                        checkbox.resumeEvents();

                        new GibsonOS.module.core.event.element.parameter.Window({
                            gos: {
                                data: record.get('returns')
                            }
                        });
                    }
                }
            }
        }];

        me.tbar = [{
            xtype: 'gosButton',
            iconCls: 'icon_system system_add',
            handler: function() {
                let node = me.getSelectionModel().getSelection();

                if (node.length) {
                    node = node[0];
                } else {
                    node = me.getRootNode();
                }

                if (node.get('leaf')) {
                    node = node.parentNode;
                }

                let newNode = node.appendChild({
                    leaf: true
                });
                node.expand();

                me.plugins[0].startEdit(newNode, 2);
            }
        }];

        me.callParent();
    }
});