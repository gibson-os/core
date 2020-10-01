Ext.define('GibsonOS.module.core.event.element.className.store.ComboBox', {
    extend: 'GibsonOS.data.Store',
    alias: ['coreEventElementClassNameComboBoxStore'],
    model: 'GibsonOS.module.core.event.element.className.model.ComboBox',
    constructor: function(data) {
        let me = this;

        me.proxy = {
            type: 'gosDataProxyAjax',
            url: baseDir + 'core/event/classNames'
        };

        me.callParent(arguments);

        return me;
    }
});