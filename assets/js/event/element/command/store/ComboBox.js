Ext.define('GibsonOS.module.core.event.element.command.store.ComboBox', {
    extend: 'GibsonOS.data.Store',
    alias: ['coreEventElementCommandComboBoxStore'],
    model: 'GibsonOS.module.core.event.element.command.model.ComboBox',
    data: [{
        command: null,
        name: 'Keins'
    },{
        command: 'if',
        name: 'Wenn'
    },{
        command: 'else',
        name: 'Sonst'
    },{
        command: 'else_if',
        name: 'Sonst wenn'
    },{
        command: 'while',
        name: 'Solange'
    },{
        command: 'do_while',
        name: 'Mache solange'
    }]
});