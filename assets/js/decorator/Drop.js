GibsonOS.define('GibsonOS.decorator.Drop', {
    init(component) {
        component = Ext.merge(component, Ext.merge({
            enableDrop: false,
            getTargetFromEvent(event) {
                if (event.getTarget('#' + this.id + '-body')?.contains(event.getTarget('.x-grid-row'))) {
                    return event.getTarget('.x-grid-row');
                }

                return event.getTarget('#' + this.id + '-body');
            },
            isDropAllowed(target, dd, event, data) {
                const me = this;

                return !!(me.id === data.dragElementId && event.getTarget('#' + me.id + '-body'));
            },
            onNodeOver(target, dd, event, data) {
                return this.isDropAllowed(target, dd, event, data)
                    ? Ext.dd.DropZone.prototype.dropAllowed
                    : Ext.dd.DropZone.prototype.dropNotAllowed
                ;
            },
            onNodeDrop(target, dd, event, data, view) {
                const me = this;
                let records = [];
                const ctrlPressed = window.event.ctrlKey;

                Ext.iterate(data.records, (record) => {
                    let recordData = record.getData();

                    if (ctrlPressed) {
                        recordData[record.idProperty] = null;
                    }

                    records.push(recordData);
                });

                if (event.getTarget('.x-grid-row')) {
                    const targetRecord = view.getRecord(target);
                    let records = data.records;

                    if (!ctrlPressed) {
                        records = [];

                        Ext.iterate(data.records, (record) => {
                            if (record !== targetRecord) {
                                records.push(record);
                            }
                        });

                        me.deleteRecords(records, data);
                    }

                    me.insertRecords(targetRecord, records, ctrlPressed, data);
                } else {
                    if (!ctrlPressed) {
                        me.deleteRecords(data.records, data);
                    }

                    me.addRecords(records, ctrlPressed, data);
                }
            },
            insertRecords(beforeRecord, records) {
                const store = this.getStore();

                store.insert(store.indexOf(beforeRecord), records);
            },
            addRecords(records) {
                this.getStore().add(records);
            },
            deleteRecords(records, data) {
                data.component.getStore().remove(records);
            }
        }, component));

        return component;
    },
    addListeners: function(component) {
        if (!component.enableDrop) {
            return;
        }

        const renderComponent = component.view ?? component;

        renderComponent.on('render', (view) => {
            component.dropZone = GibsonOS.dropZones.add(view.getEl(), {
                getTargetFromEvent(event) {
                    return component.getTargetFromEvent(event);
                },
                isDropAllowed(target, dd, event, data) {
                    return component.isDropAllowed(target, dd, event, data);
                },
                onNodeOver(target, dd, event, data) {
                    return component.onNodeOver(target, dd, event, data);
                },
                onNodeDrop(target, dd, event, data) {
                    component.onNodeDrop(target, dd, event, data, view);
                }
            });
        });
    }
});