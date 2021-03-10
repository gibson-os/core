Ext.define('GibsonOS.module.core.component.toolbar.Paging', {
    extend: 'Ext.toolbar.Paging',
    alias: ['widget.gosCoreComponentToolbarPaging'],
    border: false,
    dock: 'bottom',
    displayInfo: true,
    beforePageText: 'Seite',
    afterPageText: 'von {0}',
    displayMsg: 'Einträge {0} - {1} von {2}',
    emptyMsg: 'Keine Einträge vorhanden'
});