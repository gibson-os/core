Ext.define('GibsonOS.module.core.desktop.store.View', {
    extend: 'GibsonOS.data.Store',
    alias: ['coreDesktopViewStore'],
    model: 'GibsonOS.module.core.desktop.model.Item',
    constructor(data) {
        let me = this;

        me.proxy = {
            type: 'gosDataProxyAjax',
            url: baseDir + 'core/desktop',
            reader: {
                type: 'gosDataReaderJson',
                root: 'data.desktop'
            }
        };

        me.callParent(arguments);

        return me;
    }
});