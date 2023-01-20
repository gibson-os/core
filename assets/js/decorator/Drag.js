GibsonOS.define('GibsonOS.decorator.Drag', {
    init(component) {
        component = Ext.merge(component, Ext.merge({
            enableDrag: false,
            getShortcuts(records) {
                return null;
            }
        }, component));

        return component;
    },
    addListeners(component) {
        if (!component.enableDrag) {
            return;
        }

        component.view.on('render', (view) => {
            component.dragZone = Ext.create('Ext.dd.DragZone', view.getEl(), {
                getDragData(event) {
                    const sourceElement = event.getTarget('.x-grid-row');

                    if (sourceElement) {
                        const record = view.getRecord(sourceElement);
                        const selectedRecords = component.getSelectionModel().getSelection();
                        let records = [record];
                        const clone = sourceElement.cloneNode(true);

                        Ext.iterate(selectedRecords, (selectedRecord) => {
                            const idProperty = selectedRecord.idProperty;

                            if (record.get(idProperty) === selectedRecord.get(idProperty)) {
                                records = selectedRecords;

                                return false;
                            }
                        });

                        return component.dragData = {
                            dragElementId: component.id,
                            sourceEl: sourceElement,
                            repairXY: Ext.fly(sourceElement).getXY(),
                            ddel: clone,
                            records: records,
                            store: component.getStore(),
                            shortcut: component.getShortcuts(records)
                        };
                    }
                },
                getRepairXY() {
                    return this.dragData.repairXY;
                }
            });
        });
    }
});