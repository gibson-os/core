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
        operator: '!==',
        name: 'Ungleich'
    },{
        operator: '<',
        name: 'Kleiner'
    },{
        operator: '<=',
        name: 'Kleiner gleich'
    },{
        operator: '>',
        name: 'Größer'
    },{
        operator: '>=',
        name: 'Größer gleich'
    }]
});