Ext.define('GibsonOS.module.core.icon.AutoComplete', {
    extend: 'GibsonOS.form.AutoComplete',
    alias: ['widget.gosModuleCoreIconAutoComplete'],
    itemId: 'coreIconAutoComplete',
    emptyText: 'Keins',
    fieldLabel: 'Icon',
    url: baseDir + 'core/icon/autocomplete',
    model: 'GibsonOS.module.core.icon.model.Icon'
});