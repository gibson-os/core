GibsonOS.define('GibsonOS.decorator.Drop', {
    init(component) {
        component = Ext.merge(component, Ext.merge({
            enableDrop: false,
            getTargetFromEvent(event) {
                return event.getTarget('.x-grid-row') ?? event.getTarget('#' + component.id + '-body');
            },
            onNodeOver(target, dd, event, data) {
                if (
                    component.id === data.dragElementId &&
                    event.getTarget('#' + component.id + '-body')
                ) {
                    return Ext.dd.DropZone.prototype.dropAllowed;
                }

                return Ext.dd.DropZone.prototype.dropNotAllowed;
            },
            onNodeDrop(target, dd, event, data) {
                let records = [];
                const ctrlPressed = window.event.ctrlKey;

                Ext.iterate(data.records, (record) => {
                    let recordData = record.getData();

                    if (ctrlPressed) {
                        recordData[record.idProperty] = null;
                    }

                    records.push(recordData);
                });

                if (!ctrlPressed) {
                    Ext.iterate(data.records, (record) => {
                        record.remove();
                    });
                }

                const store = component.getStore();

                if (event.getTarget('.x-grid-row')) {
                    const node = view.getRecord(target);
                    const parentNode = node.parentNode;
                    let index = parentNode.indexOf(node);

                    Ext.iterate(records, (record) => {
                        parentNode.insertChild(index++, record);
                    });
                } else {
                    store.getRootNode().appendChild(records);
                }
            }
        }, component));

        return component;
    },
    addListeners(component) {
        if (!component.enableDrop) {
            return;
        }

        const renderComponent = component.view ?? component;

        renderComponent.on('render', (view) => {
            component.dropZone = GibsonOS.dropZones.add(view.getEl(), {
                getTargetFromEvent(event) {
                    return component.getTargetFromEvent(event);
                },
                onNodeOver(target, dd, event, data) {
                    return component.onNodeOver(target, dd, event, data);
                },
                onNodeDrop(target, dd, event, data) {
                    return component.onNodeDrop(target, dd, event, data);
                }
            });
        });
    }
});