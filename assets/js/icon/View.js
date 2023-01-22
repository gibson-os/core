Ext.define('GibsonOS.module.core.icon.View', {
    extend: 'GibsonOS.View',
    alias: ['widget.gosModuleCoreIconView'],
    itemId: 'coreIconView',
    itemSelector: 'div.iconItem',
    selectedItemCls: 'iconItemSelected',
    overItemCls: 'iconItemHover',
    overflowY: 'auto',
    multiSelect: true,
    style: 'background: white;',
    tpl: new Ext.XTemplate(
        '<tpl for=".">',
        '<div class="iconItem" title="{name}">',
        '<div class="iconItemIcon icon64 customIcon{id}"></div>',
        '<div class="iconItemName">{name}</div>',
        '</div>',
        '</tpl>'
    ),
    initComponent: function() {
        this.store = new GibsonOS.module.core.icon.store.Icon();

        this.callParent();
    }
});