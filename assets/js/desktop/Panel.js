Ext.define('GibsonOS.module.core.desktop.Panel', {
    renderTo: 'extBody',
    height: window.innerHeight,
    extend: 'GibsonOS.module.core.component.Panel',
    alias: ['widget.gosModuleCoreDesktopPanel'],
    enableToolbar: false,
    enableClickEvents: true,
    enableContextMenu: true,
    border: false,
    frame: false,
    plain: true,
    flex: 1,
    deleteFunction(records) {
        const me = this;
        let message = 'Möchtest du ' + records.length + ' Elemente wirklich löschen?';

        if (records.length === 1) {
            message = 'Möchtest du "' + records[0].get('text') + '" wirklich löschen?';
        }

        Ext.MessageBox.confirm(
            'Wirklich löschen?',
            message,
            buttonId => {
                if (buttonId === 'no') {
                    return false;
                }

                me.viewItem.getStore().remove(records);
                // saveDesktop();
            }
        );
    },
    enterFunction(record) {
        let functionName = record.get('module');
        let parameters = '';

        functionName += record.get('task').charAt(0).toUpperCase() + record.get('task').slice(1);
        functionName += record.get('action').charAt(0).toUpperCase() + record.get('action').slice(1);

        if (record.get('params')) {
            parameters = Ext.encode(record.get('params'));
        }

        if (eval('typeof(' + functionName + ') == "function"')) {
            // @todo Alter weg. Sollte so bald wie möglich raus
            eval(functionName + '(' + parameters + ')');
        } else {
            parameters = '';

            if (record.get('params')) {
                parameters = '{gos: {data: ' + Ext.encode(record.get('params')) + '}}';
            }

            functionName = 'GibsonOS.module.' + record.get('module') + '.' + record.get('task') + '.App';

            if (eval('typeof(' + functionName + ') == "function"')) {
                eval('new ' + functionName + '(' + parameters + ')');
            } else {
                GibsonOS.MessageBox.show({msg: 'Modul wurde nicht gefunden!'});
            }
        }
    },
    initComponent() {
        const me = this;
        const view = new GibsonOS.module.core.desktop.View();

        me.viewItem = view;
        me.items = [view];
        me.bbar = [{
            xtype: 'gosCoreDesktopStartMenuMenu',
            store: view.getStore()
        }];

        // me.bbar =[{
        //     xtype: 'gosButton',
        //     id: 'startmenu',
        //     iconCls: 'icon16 icon_logo',
        //     menu: [{
        //         xtype: 'gosCoreDesktopStartMenuButton',
        //         text: 'Programme',
        //         menu: [],
        //         listeners: {
        //             render(btn) {
        //                 Ext.iterate(desktopStore.getProxy().getReader().jsonData.data.apps, (app) => {
        //                     btn.menu.add({
        //                         xtype: 'gosCoreDesktopStartMenuButton',
        //                         text: app.text,
        //                         iconCls: 'icon16 ' + app.icon,
        //                         handler() {
        //                             var functionName = app.module;
        //                             functionName += app.task.charAt(0).toUpperCase() + app.task.slice(1);
        //                             functionName += app.action.charAt(0).toUpperCase() + app.action.slice(1);
        //
        //                             if (eval('typeof(' + functionName + ') == "function"')) {
        //                                 eval(functionName + '();');
        //                             } else {
        //                                 functionName = 'GibsonOS.module.' + app.module + '.'
        //                                              + app.task + '.App';
        //
        //                                 if (eval('typeof(' + functionName + ') == "function"')) {
        //                                     eval('new ' + functionName + '();');
        //                                 } else {
        //                                     GibsonOS.MessageBox.show({msg: 'Modul wurde nicht gefunden!'});
        //                                 }
        //                             }
        //                         },
        //                         listeners: {
        //                             render(btn) {
        //                                 btn.dragZone = Ext.create('Ext.dd.DragZone', btn.getEl(), {
        //                                     getDragData(event) {
        //                                         var sourceElement = event.getTarget();
        //
        //                                         if (sourceElement) {
        //                                             var clone = sourceElement.cloneNode(true);
        //                                             return btn.dragData = {
        //                                                 sourceEl: sourceElement,
        //                                                 repairXY: Ext.fly(sourceElement).getXY(),
        //                                                 ddel: clone,
        //                                                 shortcuts: [app]
        //                                             };
        //                                         }
        //                                     },
        //                                     getRepairXY() {
        //                                         return this.dragData.repairXY;
        //                                     }
        //                                 });
        //                             }
        //                         }
        //                     });
        //                 });
        //             }
        //         }
        //     },{
        //         xtype: 'gosCoreDesktopStartMenuButton',
        //         text: 'Verwaltung',
        //         menu: [{
        //             xtype: 'gosCoreDesktopStartMenuButton',
        //             text: 'Benutzer',
        //             iconCls: 'icon16 icon_user',
		//             handler() {
        //                 new GibsonOS.module.core.user.App();
		//             }
        //         },{
        //             xtype: 'gosCoreDesktopStartMenuButton',
        //             text: 'Module',
        //             iconCls: 'icon16 icon_modules',
        //             handler() {
        //                 new GibsonOS.module.core.module.App();
        //             }
        //         },{
        //             xtype: 'gosCoreDesktopStartMenuButton',
        //             text: 'Icons',
        //             handler() {
        //                 new GibsonOS.module.core.icon.App();
        //             }
        //         },{
        //             xtype: 'gosCoreDesktopStartMenuButton',
        //             text: 'Cronjobs',
        //             handler() {
        //                 new GibsonOS.module.core.cronjob.App();
        //             }
        //         },{
        //             xtype: 'gosCoreDesktopStartMenuButton',
        //             text: 'Events',
        //             handler() {
        //                 new GibsonOS.module.core.event.App();
        //             }
        //         }]
        //     },{
        //         xtype: 'gosCoreDesktopStartMenuButton',
        //         text: 'Einstellungen',
        //         iconCls: 'icon16 icon_settings',
        //         handler() {
        //             new GibsonOS.module.core.user.setting.App();
        //         }
        //     },('-'),{
        //         xtype: 'gosCoreDesktopStartMenuButton',
        //         text: 'Logout',
        //         iconCls: 'icon_system system_exit',
        //         handler() {
        //             document.location = baseDir + 'core/user/logout';
        //         }
        //     }]
        // },('-'),{
        //     xtype: 'GibsonOS.module.core.component.Panel',
		//     id: 'quicklaunch',
		//     frame: false,
		//     plain: false,
		//     flex: 0
        // },('-'),{
        //     xtype: 'GibsonOS.module.core.component.Panel',
		//     id: 'taskbar',
		//     frame: false,
		//     plain: false,
		//     flex: 0
        // },('->'),('-'),{
        //     xtype: 'gosButton',
        //     id: 'clock',
        //     enableToggle: true,
        //     timeDifference: 0,
        //     setTime() {
        //         const button = Ext.getCmp('clock');
        //         const clockWindow = Ext.getCmp('clockWindow');
        //         const localDate = new Date();
        //         const showDate = new Date(localDate.getTime() + button.timeDifference);
        //
        //         button.setText(Ext.Date.format(showDate, 'H:i'));
        //
        //         if (clockWindow) {
        //             clockWindow.update({
        //                 date: Ext.Date.format(showDate, 'l, d.m.Y (W)'),
        //                 time: Ext.Date.format(showDate, 'H:i:s'),
        //                 sunrise: Ext.Date.format(new Date(serverDate.sunrise*1000), 'H:i'),
        //                 sunset: Ext.Date.format(new Date(serverDate.sunset*1000), 'H:i')
        //             });
        //         }
        //
        //         setTimeout(button.setTime, 250);
        //     },
        //     listeners: {
        //         render(btn) {
        //             var localDate = new Date();
        //
        //             btn.timeDifference = parseInt((serverDate.now - (localDate.getTime()/1000))*1000);
        //             btn.setTime();
        //         },
        //         toggle(btn, pressed) {
        //             if (pressed) {
        //                 new GibsonOS.Window({
        //                     id: 'clockWindow',
        //                     width: 145,
        //                     autoHeight: true,
        //                     plain: true,
        //                     header: false,
        //                     data: [],
        //                     tpl: new Ext.XTemplate(
        //                         '<div id="clockDate">{date}</div>',
        //                         '<div id="clockTime">{time}</div>',
        //                         '<div id="clockSunrise">Sonnenaufgang: {sunrise}</div>',
        //                         '<div id="clockSunset">Sonnenuntergang: {sunset}</div>'
        //                     )
        //                 }).show();
        //             } else {
        //                 Ext.getCmp('clockWindow').close();
        //             }
        //         }
        //     }
        // }];

        me.callParent();

        me.addAction({
            text: 'Umbenennen',
            selectionNeeded: true,
            maxSelectionAllowed: 1,
            handler() {
                const record = me.viewItem.getSelectionModel().getSelection()[0];

                Ext.MessageBox.prompt('Neuer Name', 'Neuer Name', function(button, text) {
                    if (button !== 'ok') {
                        return;
                    }

                    record.set('text', text);
                    // saveDesktop();
                }, window, false, record.get('text'));
            }
        });
    }
});