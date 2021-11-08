Ext.define('GibsonOS.module.core.icon.View', {
    extend: 'GibsonOS.View',
    alias: ['widget.gosModuleCoreIconView'],
    itemId: 'coreIconView',
    itemSelector: 'div.icon_item',
    selectedItemCls: 'icon_item_selected',
    overItemCls: 'icon_item_hover',
    overflowY: 'auto',
    multiSelect: true,
    style: 'background: white;',
    tpl: new Ext.XTemplate(
        '<tpl for=".">',
        '<div class="icon_item" title="{name}">',
        '<div class="icon_item_icon icon64 customIcon{id}"></div>',
        '<div class="icon_item_name">{name}</div>',
        '</div>',
        '</tpl>'
    ),
    initComponent: function() {
        this.store = new GibsonOS.module.core.icon.store.Icon();

        this.callParent();
    }
});