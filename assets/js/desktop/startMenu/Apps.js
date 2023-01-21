Ext.define('GibsonOS.module.core.desktop.startMenu.Apps', {
    extend: 'GibsonOS.module.core.desktop.startMenu.Button',
    alias: ['widget.gosCoreDesktopStartMenuApps'],
    text: 'Programme',
    menu: [],
    store: null,
    initComponent() {
        const me = this;

        me.callParent();

        me.on('render', () => {
            Ext.iterate(me.store.getProxy().getReader().jsonData.data.apps, (app) => {
                me.menu.add({
                    xtype: 'gosCoreDesktopStartMenuButton',
                    text: app.text,
                    iconCls: 'icon16 ' + app.icon,
                    enableDrag: true,
                    getShortcuts(records) {
                        return records;
                    },
                    getDragRecords() {
                        return [app];
                    },
                    handler() {
                        let functionName = app.module;
                        functionName += app.task.charAt(0).toUpperCase() + app.task.slice(1);
                        functionName += app.action.charAt(0).toUpperCase() + app.action.slice(1);

                        if (eval('typeof(' + functionName + ') == "function"')) {
                            eval(functionName + '();');
                        } else {
                            functionName = 'GibsonOS.module.' + app.module + '.' + app.task + '.App';

                            if (eval('typeof(' + functionName + ') == "function"')) {
                                eval('new ' + functionName + '();');
                            } else {
                                GibsonOS.MessageBox.show({msg: 'Modul wurde nicht gefunden!'});
                            }
                        }
                    },
                    // listeners: {
                    //     render(button) {
                    //         button.dragZone = Ext.create('Ext.dd.DragZone', button.getEl(), {
                    //             getDragData(event) {
                    //                 const sourceElement = event.getTarget();
                    //
                    //                 if (sourceElement) {
                    //                     const clone = sourceElement.cloneNode(true);
                    //                     return button.dragData = {
                    //                         sourceEl: sourceElement,
                    //                         repairXY: Ext.fly(sourceElement).getXY(),
                    //                         ddel: clone,
                    //                         shortcuts: [app]
                    //                     };
                    //                 }
                    //             },
                    //             getRepairXY() {
                    //                 return this.dragData.repairXY;
                    //             }
                    //         });
                    //     }
                    // }
                });
            });
        });
    }
});