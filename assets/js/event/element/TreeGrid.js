Ext.define('GibsonOS.module.core.event.element.TreeGrid', {
    extend: 'GibsonOS.module.core.component.tree.Panel',
    alias: ['widget.gosModuleCoreEventElementTreeGrid'],
    autoScroll: true,
    useArrows: true,
    multiSelect: true,
    enableDrag: true,
    enableDrop: true,
    requiredPermission: {
        module: 'core',
        task: 'event'
    },
    initComponent: function () {
        let me = this;

        me.store = new GibsonOS.module.core.event.element.store.TreeGrid();

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
                        let returnCheckbox = form.findField('hasReturn');

                        methodComboBox.disable();
                        parametersCheckbox.disable();
                        returnCheckbox.disable();

                        if (!record.get('className')) {
                            return;
                        }

                        let methodComboBoxStore = methodComboBox.getStore();

                        methodComboBoxStore.getProxy().setExtraParam('className', record.get('className'));
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
                            if (!Ext.Object.isEmpty(methodRecord.get('parameters'))) {
                                Ext.iterate(methodRecord.get('parameters'), function(name, parameter) {
                                    if (!record.get('parameters')[name]) {
                                        return true;
                                    }

                                    parameter.value = record.get('parameters')[name].value;
                                });

                                parametersCheckbox.enable();
                            }

                            if (!Ext.Object.isEmpty(methodRecord.get('returns'))) {
                                Ext.iterate(methodRecord.get('returns'), function(name, parameter) {
                                    if (!record.get('returns')[name]) {
                                        return true;
                                    }

                                    parameter.value = record.get('returns')[name].value;
                                    parameter.operator = record.get('returns')[name].operator;
                                });

                                returnCheckbox.enable();
                            }
                        });
                        methodComboBox.enable();
                    }
                }
            })
        ];

        me.addButton = {
            requiredPermission: {
                action: '',
                method: 'POST',
                permission: GibsonOS.Permission.WRITE
            }
        };
        me.deleteButton = {
            requiredPermission: {
                action: '',
                method: 'DELETE',
                permission: GibsonOS.Permission.DELETE
            }
        };

        me.callParent();
    },
    addFunction: function() {
        let me = this;
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
    },
    deleteFunction: function(nodes) {
        Ext.iterate(nodes, (node) => {
            node.remove();
        });
    },
    getColumns: function() {
        let me = this;

        return [{
            xtype: 'treecolumn'
        },{
            xtype: 'gosGridColumnComboBoxEditor',
            itemId: 'gosModuleCoreEventElementTreeGridColumnCommand',
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
            },
            renderer: function(value) {
                let record = me.down('#gosModuleCoreEventElementTreeGridColumnCommand').getEditor().findRecordByValue(value);

                return record === false ? null : record.get('name');
            }
        },{
            xtype: 'gosGridColumnComboBoxEditor',
            itemId: 'gosModuleCoreEventElementTreeGridColumnClassName',
            header: 'Klasse',
            dataIndex: 'className',
            flex: 1,
            editor: {
                xtype: 'gosModuleCoreEventElementClassNameComboBox',
                editable: true,
                listeners: {
                    change: function(comboBox, newValue) {
                        let methodColumnEditor = me.down('#gosModuleCoreEventElementTreeGridColumnMethod').getEditor();
                        let methodColumnEditorStore = methodColumnEditor.getStore();

                        methodColumnEditor.setValue(null);

                        methodColumnEditorStore.getProxy().setExtraParam('className', newValue);
                        methodColumnEditorStore.load();

                        methodColumnEditor.enable();
                    }
                }
            },
            renderer: function(value, metaData, record) {
                let column = me.down('#gosModuleCoreEventElementTreeGridColumnClassName');
                let comboBox = column.getEditor();
                let comboRecord = comboBox.findRecordByValue(value);

                return comboBox.getStore().count() === 0 ? record.get('classNameTitle') :
                    comboRecord === false ? null : comboRecord.get('title');
            }
        },{
            xtype: 'gosGridColumnComboBoxEditor',
            header: 'Methode',
            itemId: 'gosModuleCoreEventElementTreeGridColumnMethod',
            dataIndex: 'method',
            flex: 1,
            editor: {
                xtype: 'gosModuleCoreEventElementMethodComboBox',
                editable: true,
                listeners: {
                    change: function(comboBox, newValue) {
                        let record = comboBox.findRecordByValue(newValue);
                        let parametersCheckbox = me.down('#gosModuleCoreEventElementTreeGridColumnParameters').getEditor();
                        let returnCheckbox = me.down('#gosModuleCoreEventElementTreeGridColumnReturn').getEditor();
                        let parameters = null;
                        let returns = null;

                        parametersCheckbox.disable();
                        returnCheckbox.disable();

                        if (record) {
                            parameters = record.get('parameters');
                            returns = record.get('returns');

                            if (!Ext.Object.isEmpty(parameters)) {
                                parametersCheckbox.enable();
                            }

                            if (!Ext.Object.isEmpty(returns)) {
                                returnCheckbox.enable();
                            }
                        }
                    }
                }
            },
            renderer: function(value, metaData, record) {
                let column = me.down('#gosModuleCoreEventElementTreeGridColumnMethod');
                let comboBox = column.getEditor();
                let comboRecord = comboBox.findRecordByValue(value);

                return comboBox.getStore().count() === 0 ? record.get('methodTitle') :
                    comboRecord === false ? null : comboRecord.get('title');
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
                let parameters = record.get('parameters');

                if (methodComboBoxRecord) {
                    parameters = methodComboBoxRecord.get('parameters');
                }

                if (!parameters) {
                    return returnValue;
                }

                Ext.iterate(parameters, function(name, parameter) {
                    returnValue += parameter.title + ': ';

                    if (values[name]) {
                        returnValue += values[name].value !== null ? values[name].value : '';
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

                        new GibsonOS.module.core.parameter.Window()
                            .down('gosModuleCoreParameterForm').addFields(record.get('parameters'))
                        ;
                    }
                }
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
                let returns = record.get('returns');

                if (methodComboBoxRecord) {
                    returns = methodComboBoxRecord.get('returns');
                }

                if (!returns) {
                    return returnValue;
                }

                Ext.iterate(returns, function(name, parameter) {
                    if (!values[name] || values[name].value === null) {
                        return true;
                    }

                    returnValue +=
                        parameter.title + ' ' +
                        (values[name].operator ? values[name].operator + ' ' : ' ') +
                        values[name].value +
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
                        let methodComboBox = me.down('#gosModuleCoreEventElementTreeGridColumnMethod').getEditor();
                        let methodComboBoxRecord = methodComboBox.findRecordByValue(methodComboBox.getValue());
                        let record = me.getSelectionModel().getSelection()[0];
                        record.set('returns', methodComboBoxRecord.get('returns'));

                        checkbox.suspendEvents();
                        checkbox.setValue(true);
                        checkbox.resumeEvents();

                        new GibsonOS.module.core.parameter.Window({
                            withOperator: true,
                            withSet: true
                        })
                            .down('gosModuleCoreParameterForm').addFields(record.get('returns'))
                        ;
                    }
                }
            }
        }];
    }
});