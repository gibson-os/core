Ext.define('GibsonOS.module.core.component.form.field.AutoComplete', {
    extend: 'GibsonOS.module.core.component.form.field.ComboBox',
    alias: ['widget.gosCoreComponentFormFieldAutoComplete'],
    editable: true,
    queryMode: 'remote',
    queryParam: 'name',
    queryParamId: 'id',
    minChars: 2,
    isSetting: false,
    store: {
        xtype: 'gosDataStore',
        autoLoad: false,
        proxy: {
            type: 'gosDataProxyAjax',
            reader: {
                type: 'gosDataReaderJson'
            }
        }
    },
    initComponent: function() {
        const me = this;
        me.store.model = this.model;
        me.store.proxy.url = this.url;

        if (me.params) {
            me.store.proxy.extraParams = this.params;
        }

        me.callParent();

        if (me.value) {
            me.setValueById(this.value);
        }
    },
    setValue: function(value, doSelect) {
        const me = this;

        if (
            !!value &&
            typeof(value) !== 'object' &&
            !me.isSetting
        ) {
            if (typeof(value) === 'object') {
                value = value.data[me.valueField];
            }

            me.setValueById(value);
        } else {
            if (!Array.isArray(value)) {
                value = [value];
            }

            me.callParent(value, !!doSelect);
        }
    },
    setValueById: function(value) {
        const me = this;
        let params = {};
        me.isSetting = true;

        if (!value) {
            me.setValue(value);
            return true;
        }

        if (me.params) {
            params = me.params;
        } else {
            me.params = params;
        }

        params[me.queryParamId] = value;

        me.getStore().getProxy().extraParams = params;
        me.getStore().load(function(records) {
            me.select(records[0]);

            delete params[me.queryParamId];
            me.isSetting = false;
        });
    }
});
