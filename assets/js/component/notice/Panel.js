Ext.define('GibsonOS.module.core.component.notice.Panel', {
    extend: 'Ext.Panel',
    alias: ['widget.gosCoreComponentNoticePanel'],
    border: false,
    frame: false,
    plain: true,
    flex: 1,
    type: 'error',
    text: '',
    initComponent() {
        let me = this;

        me.data = {
            type: me.type,
            text: me.text
        }

        me.tpl = new Ext.XTemplate(
            '<div class="notice notice{[values.type.charAt(0).toUpperCase()]}{[values.type.slice(1)]}">',
                '<div class="noticeIcon"></div>',
                '<div>{text}</div>',
            '</div>'
        );

        me.callParent();
    }
});