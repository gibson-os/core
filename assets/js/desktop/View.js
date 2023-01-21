Ext.define('GibsonOS.module.core.desktop.View', {
    extend: 'GibsonOS.module.core.component.view.View',
    alias: ['widget.gosCoreDesktopView'],
    id: 'desktop',
    height: window.innerHeight-25,
    frame: false,
    plain: true,
    border: false,
    flex: 0,
    autoHeight: true,
    multiSelect: true,
    singleSelect: false,
    trackOver: true,
    itemSelector: 'div.desktop_item',
    selectedItemCls: 'desktop_item_selected',
    overItemCls: 'desktop_item_hover',
    activeDropZone: null,
    itemContextMenu: [],
    enableDrag: true,
    getShortcuts(records) {
        let shortcuts = [];

        Ext.iterate(records, (record) => {
            record.move = true;
            shortcuts.push(record);
        });

        return shortcuts;
    },
    enableDrop: true,
    getTargetFromEvent(event) {
        const me = this;

        if (event.getTarget('.x-window') != null) {
            let target = null;

            Ext.iterate(GibsonOS.dropZones.zones, function(elementId, dropZone) {
                target = dropZone.getTargetFromEvent(event);

                if (!target) {
                    return true;
                }

                me.activeDropZone = dropZone;

                return false;
            });

            if (!target) {
                view.activeDropZone = null;
            }

            return target;
        }

        me.activeDropZone = null;

        if (event.getTarget('.desktop_item') != null) {
            return event.getTarget('.desktop_item');
        }

        return event.getTarget('#desktop');
    },
    onNodeOver : function(target, dd, event, data) {
        const me = this;

        if (me.activeDropZone) {
            return me.activeDropZone.onNodeOver(target, dd, event, data);
        }
console.log(data);
console.log(data.shortcuts);
        if (data.shortcuts) {
            return Ext.dd.DropZone.prototype.dropAllowed;
        }

        me.activeDropZone = null;

        return Ext.dd.DropZone.prototype.dropNotAllowed;
    },
    onNodeDrop: function(target, dd, event, data) {
        const me = this;

        if (me.activeDropZone) {
            return me.activeDropZone.onNodeDrop(target, dd, event, data);
        }

        if (!data.shortcuts && data.shortcuts) {
            return;
        }

        if (data.shortcuts && data.shortcuts[0].move) {
            me.getStore().remove(data.shortcuts);
        }

        if (event.getTarget('.desktop_item') != null) {
            const record = me.getRecord(target);
            me.getStore().insert(me.getStore().indexOf(record), data.shortcuts);
        } else {
            me.getStore().add(data.shortcuts);
        }

        // saveDesktop();
    },
    initComponent() {
        const me = this;

        me.store = new GibsonOS.module.core.desktop.store.View();

        me.tpl = new Ext.XTemplate(
            '<tpl for=".">',
                '<div class="desktop_item" title="{text}">',
                    '<tpl if="thumb">',
                        '<div class="desktop_item_icon icon64" style="background-image: url(data:image/png;base64,{thumb});"></div>',
                    '<tpl else>',
                        '<div class="desktop_item_icon icon64 <tpl if="customIcon &gt; 0">customIcon{customIcon}<tpl else>{icon}</tpl>"></div>',
                    '</tpl>',
                    '<div class="desktop_item_name">{text}</div>',
                '</div>',
            '</tpl>'
        );

        me.callParent();
    }
});