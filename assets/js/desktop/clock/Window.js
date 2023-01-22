Ext.define('GibsonOS.module.core.desktop.clock.Window', {
    extend: 'GibsonOS.Window',
    alias: ['widget.gosCoreDesktopClockWindow'],
    id: 'clockWindow',
    width: 145,
    autoHeight: true,
    plain: true,
    header: false,
    clockButton: null,
    data: [],
    tpl: new Ext.XTemplate(
        '<div id="clockDate">{date}</div>',
        '<div id="clockTime">{time}</div>',
        '<div id="clockSunrise">Sonnenaufgang: {sunrise}</div>',
        '<div id="clockSunset">Sonnenuntergang: {sunset}</div>'
    ),
    initComponent() {
        me = this;

        me.clockButton.on('updateTime', (button, date) => {
            me.update({
                date: Ext.Date.format(date, 'l, d.m.Y (W)'),
                time: Ext.Date.format(date, 'H:i:s'),
                sunrise: Ext.Date.format(new Date(serverDate.sunrise*1000), 'H:i'),
                sunset: Ext.Date.format(new Date(serverDate.sunset*1000), 'H:i')
            });
        });

        me.callParent();
    }
});