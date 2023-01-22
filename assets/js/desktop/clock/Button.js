Ext.define('GibsonOS.module.core.desktop.clock.Button', {
    extend: 'GibsonOS.Button',
    alias: ['widget.gosCoreDesktopClockButton'],
    enableToggle: true,
    timeDifference: 0,
    window: null,
    initComponent() {
        const me = this;

        me.callParent();

        me.on('render', () => {
            const localDate = new Date();

            me.timeDifference = parseInt((serverDate.now - (localDate.getTime()/1000))*1000);
            me.setTime(me);
        });
        me.on('toggle', (button, pressed) => {
            if (!pressed) {
                me.window.close();
                me.window = null;

                return;
            }

            me.window = new GibsonOS.module.core.desktop.clock.Window({
                clockButton: me,
            }).show();
        })
    },
    setTime(button) {
        const localDate = new Date();
        const showDate = new Date(localDate.getTime() + button.timeDifference);

        button.setText(Ext.Date.format(showDate, 'H:i'));
        button.fireEvent('updateTime', button, showDate);

        setTimeout(button.setTime, 250, button);
    },
});