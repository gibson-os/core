Ext.define('GibsonOS.module.core.component.form.FieldContainer', {
    extend: 'Ext.form.FieldContainer',
    alias: ['widget.gosCoreComponentFormFieldContainer'],
    anchor: '100%',
    fieldLabel: 'Felder',
    layout: 'hbox',
    defaults: {
        flex: 1,
        hideLabel: true
    },
    validate() {
        const me = this;
        let valid = true;

        me.items.each((item) => {
            if (typeof item.validate !== 'function') {
                return true;
            }

            valid = item.validate();

            if (!valid) {
                return false;
            }
        });

        return valid;
    },
    isFileUpload() {
        const me = this;
        let fileUpload = false;

        me.items.each((item) => {
            if (typeof item.isFileUpload !== 'function') {
                return true;
            }

            fileUpload = item.isFileUpload();

            if (fileUpload) {
                return false;
            }
        });

        return fileUpload;
    },
    getModelData(includeEmptyText) {
        const me = this;
        let modelData = {};

        me.items.each((item) => {
            if (typeof item.getModelData !== 'function') {
                return true;
            }

            modelData = {...modelData, ...item.getModelData(includeEmptyText)};
        });

        return modelData;
    },
    getSubmitData(includeEmptyText) {
        const me = this;
        let submitData = {};

        me.items.each((item) => {
            if (typeof item.getSubmitData !== 'function') {
                return true;
            }

            submitData = {...submitData, ...item.getSubmitData(includeEmptyText)};
        });

        return submitData;
    }
});