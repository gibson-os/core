GibsonOS.define('GibsonOS.event.action.Execute', {
    init: (component) => {
        component.addAction({
            xtype: 'tbseparator',
            addToContainerContextMenu: false,
        });
        component.addAction({
            iconCls: 'icon_system system_play',
            text: 'Ausführen',
            selectionNeeded: true,
            eventId: null,
            listeners: {
                click: () => {
                    let record = component.getSelectionModel().getSelection()[0];
                    component.setLoading(true);

                    GibsonOS.Ajax.request({
                        url: baseDir + 'core/event/run',
                        params: {
                            eventId: record.get('id')
                        },
                        callback: function() {
                            component.setLoading(false);
                        }
                    });
                }
            }
        });
    }
});