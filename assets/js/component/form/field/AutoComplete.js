Ext.define('GibsonOS.module.core.component.form.field.AutoComplete', {
    extend: 'GibsonOS.module.core.component.form.field.ComboBox',
    alias: ['widget.gosCoreComponentFormFieldAutoComplete'],
    editable: true,
    queryMode: 'remote',
    queryParam: 'name',
    queryParamId: 'id',
    minChars: 0,
    isSetting: false,
    store: {
        autoLoad: false,
        proxy: {
            type: 'gosDataProxyAjax',
            reader: {
                type: 'gosDataReaderJson'
            }
        }
    },
    initComponent() {
        const me = this;
        me.store.model = me.model;
        me.store.proxy.model = me.model;
        me.store.proxy.url = me.url;

        if (me.params) {
            me.store.proxy.extraParams = this.params;
        }

        me.callParent();

        if (me.value) {
            me.setValueById(me.value);
        }
    },
    setValue(value, doSelect) {
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
    setValueById(value) {
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
        me.getStore().load((records) => {
            if (records && records.length) {
                me.select(records[0]);
            }
            me.isSetting = false;
        });
        delete params[me.queryParamId];
        me.getStore().getProxy().extraParams = params;
    }
});
