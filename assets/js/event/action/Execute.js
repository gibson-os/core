GibsonOS.define('GibsonOS.event.action.Execute', {
    init(component) {
        const me = this;

        component.addAction({
            iconCls: 'icon_system system_play',
            text: 'Ausführen',
            selectionNeeded: true,
            eventId: null,
            listeners: {
                click() {
                    me.run(component, component.getSelectionModel().getSelection()[0].get('id'));
                }
            }
        });
    },
    run(component, eventId) {
        let me = this;
        component.setLoading(true);

        GibsonOS.Ajax.request({
            url: baseDir + 'core/event/run',
            params: {
                eventId: eventId
            },
            callback: function() {
                component.setLoading(false);
            }
        });

    }
});