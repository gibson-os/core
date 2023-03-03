Ext.define('GibsonOS.module.core.component.form.Panel', {
    extend: 'Ext.form.Panel',
    alias: ['widget.gosCoreComponentFormPanel'],
    buttonAlign: 'center',
    frame: true,
    flex: 1,
    defaults: {
        xtype: 'gosCoreComponentPanel'
    },
    enableToolbar: false,
    enableKeyEvents: false,
    enableClickEvents: false,
    enableContextMenu: false,
    url: null,
    params: {},
    initComponent() {
        let me = this;

        me = GibsonOS.decorator.Panel.init(me);

        if (!me.dockedItems) {
            me.dockedItems = [];
        }

        me.dockedItems.push({
            xtype: 'toolbar',
            dock: 'bottom',
            ui: 'footer',
            itemId: 'buttons',
            defaults: {
                minWidth: me.minButtonWidth
            }
        });

        me.callParent();

        GibsonOS.decorator.Panel.addListeners(me);

        const basicForm = me.getForm();
        basicForm.on('actioncomplete', (form, action) => {
            const responseText = Ext.decode(action.response.responseText);
            GibsonOS.checkResponseForLogin(responseText);
            GibsonOS.checkResponseForErrorMessage(responseText, action);

            form.getFields().each((field) => {
                field.originalValue = field.getValue();
            });
        }, this, {
            priority: 999
        });
        basicForm.on('actionfailed', (form, action) => {
            let responseText = Ext.decode(action.response.responseText);
            GibsonOS.checkResponseForLogin(responseText);
            GibsonOS.checkResponseForErrorMessage(responseText, action);
        }, this, {
            priority: 999
        });

        if (me.url !== null) {
            me.on('render', () => {
                me.loadForm(me.url, me.params);
            });
        }
    },
    loadForm(url, params) {
        const me = this;

        me.setLoading(true);

        GibsonOS.Ajax.request({
            url: url,
            params: params,
            success(response) {
                const data = Ext.decode(response.responseText).data;
                const buttons = me.down('#buttons');

                me.removeAll();
                buttons.removeAll();
                buttons.add({
                    xtype: 'component',
                    flex: 1
                });
                me.addFields(data.fields);
                me.addButtons(data.buttons);
                buttons.add({
                    xtype: 'component',
                    flex: 1
                });
                me.setLoading(false);
            }
        });
    },
    addButton(name, button) {
        const me = this;

        me.down('#buttons').add({
            text: button.text,
            handler() {
                if (button.module === null) {
                    return;
                }

                me.setLoading(true);

                const form = me.getForm();

                form.submit({
                    xtype: 'gosFormActionAction',
                    itemId: name,
                    requiredPermission: {
                        module: button.module,
                        task: button.task,
                        action: button.action
                    },
                    url: baseDir + button.module + '/' + button.task + '/' + button.action,
                    params: button.parameters,
                    failure() {
                        me.setLoading(false);
                    },
                    success() {
                        me.setLoading(false);
                    }
                });
            }
        });
    },
    addButtons(buttons) {
        const me = this;

        Ext.iterate(buttons, (name, button) => {
            me.addButton(name, button);
        });
    },
    addField(name, parameter) {
        const me = this;

        me.fireEvent('beforeAddField', name, parameter);

        let item = {
            xtype: parameter.xtype,
            name: name,
            value: parameter.value ?? null,
            checked: parameter.value === true,
            parameterObject: parameter,
            fieldLabel: parameter.title
        };

        if (parameter.config.inputType) {
            item.inputType = parameter.config.inputType;
        }

        if (parameter.config.options) {
            item.store = {
                fields: ['id', 'name'],
                data: []
            }

            Ext.iterate(parameter.config.options, (value, id) => {
                item.store.data.push({
                    id: id,
                    name: value
                });
            });
        }

        if (parameter.image) {
            me.add({
                xtype: 'gosCoreComponentFormFieldDisplay',
                value: '<img src="' + parameter.image + '" alt="image" />',
                fieldLabel: '&nbsp;',
                labelSeparator: ''
            });
        }

        me.add(item);
        me.fireEvent('afterAddField', name, parameter);
    },
    addFields(parameters) {
        const me = this;

        me.fireEvent('beforeAddFields', parameters);

        Ext.iterate(parameters, (name, parameter) => {
            me.addField(name, parameter)
        });

        me.fireEvent('afterAddFields', parameters);
    }
});