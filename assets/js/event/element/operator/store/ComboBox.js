Ext.define('GibsonOS.module.core.event.element.operator.store.ComboBox', {
    extend: 'GibsonOS.data.Store',
    alias: ['coreEventElementOperatorComboBoxStore'],
    model: 'GibsonOS.module.core.event.element.operator.model.ComboBox',
    data: [{
        operator: null,
        name: 'Keiner'
    },{
        operator: '=',
        name: 'Setzen'
    },{
        operator: '===',
        name: 'Gleich'
    },{
        command: '!==',
        name: 'Ungleich'
    },{
        command: '<',
        name: 'Kleiner'
    },{
        command: '<=',
        name: 'Kleiner gleich'
    },{
        command: '>',
        name: 'Größer'
    },{
        command: '>=',
        name: 'Größer gleich'
    }]
});