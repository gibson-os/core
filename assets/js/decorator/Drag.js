GibsonOS.define('GibsonOS.decorator.Drag', {
    init(component) {
        component = Ext.merge(component, Ext.merge({
            enableDrag: false,
            getShortcuts() {
                return null;
            },
            getSourceElement(event) {
                return event.getTarget('.x-grid-row');
            },
            getDragRecords(view, sourceElement) {
                const record = view.getRecord(sourceElement);
                const selectedRecords = component.getSelectionModel().getSelection();
                let records = [record];

                Ext.iterate(selectedRecords, (selectedRecord) => {
                    if (record.getId() === selectedRecord.getId()) {
                        records = selectedRecords;

                        return false;
                    }
                });

                return records;
            }
        }, component));

        return component;
    },
    addListeners(component) {
        if (!component.enableDrag) {
            return;
        }

        const renderComponent = component.view ?? component;

        renderComponent.on('render', (view) => {
            component.dragZone = Ext.create('Ext.dd.DragZone', view.getEl(), {
                getDragData(event) {
                    const sourceElement = component.getSourceElement(event);

                    if (!sourceElement) {
                        return;
                    }

                    const clone = sourceElement.cloneNode(true);
                    const records = component.getDragRecords(view, sourceElement);

                    return component.dragData = {
                        dragElementId: component.id,
                        sourceEl: sourceElement,
                        repairXY: Ext.fly(sourceElement).getXY(),
                        ddel: clone,
                        records: records,
                        component: component,
                        shortcuts: component.getShortcuts(records)
                    };
                },
                getRepairXY() {
                    return this.dragData.repairXY;
                }
            });
        });
    }
});