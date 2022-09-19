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

        me.callParent();

        GibsonOS.decorator.Panel.addListeners(me);

        const basicForm = me.getForm();
        basicForm.on('actioncomplete', function(form, action) {
            let responseText = Ext.decode(action.response.responseText);
            GibsonOS.checkResponseForLogin(responseText);
            GibsonOS.checkResponseForErrorMessage(responseText, action);

            form.getFields().each(function(field) {
                field.originalValue = field.getValue();
            });
        }, this, {
            priority: 999
        });
        basicForm.on('actionfailed', function(form, action) {
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
                me.removeAll();
                me.addFields(Ext.decode(response.responseText).data.fields);
                me.setLoading(false);
            }
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