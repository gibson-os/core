Ext.define('GibsonOS.module.core.desktop.View', {
    extend: 'GibsonOS.module.core.component.view.View',
    alias: ['widget.gosCoreDesktopView'],
    id: 'gosDesktop',
    height: window.innerHeight-25,
    frame: false,
    plain: true,
    border: false,
    flex: 0,
    autoHeight: true,
    multiSelect: true,
    singleSelect: false,
    trackOver: true,
    itemSelector: 'div.desktopItem',
    selectedItemCls: 'desktopItemSelected',
    overItemCls: 'desktopItemHover',
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

            Ext.iterate(GibsonOS.dropZones.zones, (elementId, dropZone) => {
                if (elementId === me.id) {
                    return true;
                }

                target = dropZone.getTargetFromEvent(event);

                if (!target) {
                    return true;
                }

                me.activeDropZone = dropZone;

                return false;
            });

            if (!target) {
                me.activeDropZone = null;
            }

            return target;
        }

        me.activeDropZone = null;

        if (event.getTarget('.desktopItem') != null) {
            return event.getTarget('.desktopItem');
        }

        return event.getTarget('#gosDesktop');
    },
    isDropAllowed(target, dd, event, data) {
        const me = this;

        if (me.activeDropZone) {
            return me.activeDropZone.isDropAllowed(target, dd, event, data);
        }

        if (data.shortcuts) {
            return true;
        }

        me.activeDropZone = null;

        return false;
    },
    onNodeDrop(target, dd, event, data) {
        const me = this;

        if (me.activeDropZone) {
            if (!me.activeDropZone.isDropAllowed(target, dd, event, data)) {
                return;
            }

            me.activeDropZone.onNodeDrop(target, dd, event, data);

            return;
        }

        if (!data.shortcuts && data.shortcuts) {
            return;
        }

        const store = me.getStore();

        if (data.shortcuts && data.shortcuts[0].move) {
            store.remove(data.shortcuts);
        }

        let position = store.count();
        let start = position;

        if (event.getTarget('.desktopItem') !== null) {
            const targetRecord = me.getRecord(target);

            position = targetRecord.get('position');
            start = store.indexOf(targetRecord);
        }

        const records = me.getRecordsWithPosition(data.shortcuts, position);

        if (data.shortcuts[0].move) {
            Ext.iterate(store.getRange(start), (record) => {
                record.set('position', record.get('position') + records.length);
            });

            store.insert(start, records);
            me.saveDesktop();

            return;
        }

        GibsonOS.Ajax.request({
            url: baseDir + 'core/desktop/add',
            params: {
                items: Ext.encode(records)
            },
            success(response) {
                Ext.iterate(store.getRange(start), (record) => {
                    record.set('position', record.get('position') + records.length);
                });

                store.insert(start, Ext.decode(response.responseText).data);
            }
        });
    },
    initComponent() {
        const me = this;

        me.store = new GibsonOS.module.core.desktop.store.View();

        me.tpl = new Ext.XTemplate(
            '<tpl for=".">',
                '<div class="desktopItem" title="{text}">',
                    '<tpl if="thumb">',
                        '<div class="desktopItemIcon icon64" style="background-image: url(data:image/png;base64,{thumb});"></div>',
                    '<tpl else>',
                        '<div class="desktopItemIcon icon64 <tpl if="customIcon &gt; 0">customIcon{customIcon}<tpl else>{icon}</tpl>"></div>',
                    '</tpl>',
                    '<div class="desktopItemName">{text}</div>',
                '</div>',
            '</tpl>'
        );

        me.callParent();
    },
    getRecordsWithPosition(records, position = 0) {
        let newRecords = [];

        Ext.iterate(records, (record) => {
            if (!record.id) {
                record.id = 0;
            }

            // if (typeof(record.parameters) !== 'object') {
            //     record.parameters = {};
            // }

            record.position = position++;
            newRecords.push(record);
        });

        return newRecords;
    },
    saveDesktop() {
        const me = this;
        let records = [];

        me.getStore().each((record) => {
            let data = record.getData();

            if (data.parameters === '') {
                data.parameters = {};
            }

            records.push(data);
        });

        GibsonOS.Ajax.request({
            url: baseDir + 'core/desktop/save',
            params: {
                items: Ext.encode(records)
            },
            success(response) {
                const data = Ext.decode(response.responseText).data;
                let index = 0;

                me.getStore().each((record) => {
                    Ext.iterate(data[index++], (field, value) => {
                        record.set(field, value);
                    });
                });
            }
        });
    }
});