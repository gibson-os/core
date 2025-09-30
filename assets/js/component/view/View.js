Ext.define('GibsonOS.module.core.component.view.View', {
    extend: 'Ext.view.View',
    alias: ['widget.gosCoreComponentViewView'],
    border: false,
    flex: 1,
    frame: false,
    plain: true,
    getSourceElement(event) {
        return event.getTarget(this.itemSelector, 10);
    },
    initComponent() {
        let me = this;

        me = GibsonOS.decorator.Drag.init(me);
        me = GibsonOS.decorator.Drop.init(me);
        me = GibsonOS.decorator.AutoReload.init(me);

        me.callParent();

        GibsonOS.decorator.Drag.addListeners(me);
        GibsonOS.decorator.Drop.addListeners(me);
        GibsonOS.decorator.AutoReload.addListeners(me);
    }
});