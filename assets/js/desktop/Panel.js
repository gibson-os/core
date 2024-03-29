Ext.define('GibsonOS.module.core.desktop.Panel', {
    renderTo: 'extBody',
    height: window.innerHeight,
    extend: 'GibsonOS.module.core.component.Panel',
    alias: ['widget.gosModuleCoreDesktopPanel'],
    id: 'gosDesktopContainer',
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
                me.viewItem.saveDesktop();
            }
        );
    },
    enterButton: {
        text: 'Öffnen',
        iconCls: null,
    },
    enterFunction(record) {
        let functionName = record.get('module');
        let parameters = '';

        functionName += record.get('task').charAt(0).toUpperCase() + record.get('task').slice(1);
        functionName += record.get('action').charAt(0).toUpperCase() + record.get('action').slice(1);

        if (record.get('parameters')) {
            parameters = Ext.encode(record.get('parameters'));
        }

        if (eval('typeof(' + functionName + ') == "function"')) {
            // @todo Alter weg. Sollte so bald wie möglich raus
            eval(functionName + '(' + parameters + ')');
        } else {
            parameters = '';

            if (record.get('parameters')) {
                parameters = '{gos: {data: ' + Ext.encode(record.get('parameters')) + '}}';
            }

            const actionName = 'GibsonOS.module.' + record.get('module') + '.' + record.get('task') + '.' + record.get('action');
            functionName = 'GibsonOS.module.' + record.get('module') + '.' + record.get('task') + '.' + record.get('action') + '.App';

            if (eval('typeof(' + actionName + ') != "undefined" && typeof(' + functionName + ') == "function"')) {
                eval('new ' + functionName + '(' + parameters + ')');
            } else {
                functionName = 'GibsonOS.module.' + record.get('module') + '.' + record.get('task') + '.App';

                if (eval('typeof(' + functionName + ') == "function"')) {
                    eval('new ' + functionName + '(' + parameters + ')');
                } else {
                    GibsonOS.MessageBox.show({msg: 'Modul wurde nicht gefunden!'});
                }
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
        },('-'),{
            xtype: 'gosCoreDesktopQuickLaunchPanel'
        },{
            xtype: 'gosCoreDesktopTaskbarPanel'
        },('->'),('-'),{
            xtype: 'gosCoreDesktopClockButton'
        }];

        me.callParent();

        me.addAction({
            text: 'Umbenennen',
            selectionNeeded: true,
            maxSelectionAllowed: 1,
            handler() {
                const record = me.viewItem.getSelectionModel().getSelection()[0];

                Ext.MessageBox.prompt('Neuer Name', 'Neuer Name', (button, text) => {
                    if (button !== 'ok') {
                        return;
                    }

                    record.set('text', text);
                    view.saveDesktop();
                }, window, false, record.get('text'));
            }
        });

        me.on('render', () => {
            window.onresize = () => {
                me.setHeight(window.innerHeight);
                me.setWidth(window.innerWidth);
                me.viewItem.setHeight(window.innerHeight-25);
                me.viewItem.setWidth(window.innerWidth);
            };

            setTimeout(me.sessionRefresh, 60000);
        })
    },
    sessionRefresh() {
        const me = this;

        GibsonOS.Ajax.request({
            url: baseDir + 'core/user/sessionRefresh',
            success() {
                setTimeout(me.sessionRefresh, 60000);
            }
        });
    }
});