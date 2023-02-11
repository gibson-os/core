Ext.define('GibsonOS.module.core.event.App', {
    extend: 'GibsonOS.App',
    alias: ['widget.gosModuleCoreEventApp'],
    title: 'Events',
    width: 600,
    height: 400,
    requiredPermission: {
        module: 'core',
        task: 'event'
    },
    initComponent() {
        let me = this;

        me.items = [{
            xtype: 'gosModuleCoreEventGrid'
        }];

        me.callParent();

        if (me.gos.data.eventId) {
            GibsonOS.MessageBox.show({
                title: 'Event ausführen?',
                msg: 'Soll das Event ausgeführt werden?',
                type: GibsonOS.MessageBox.type.QUESTION,
                buttons: [{
                    text: 'Ja',
                    handler() {
                        GibsonOS.event.action.Execute.run(me, me.gos.data.eventId);
                    }
                },{
                    text: 'Nein'
                }]
            });
        }
    }
});