Ext.define('GibsonOS.module.core.event.element.operator.store.ComboBox', {
    extend: 'GibsonOS.data.Store',
    alias: ['coreEventElementOperatorComboBoxStore'],
    model: 'GibsonOS.module.core.event.element.operator.model.ComboBox',
    exclude: [],
    data: [],
    constructor(data) {
        const me = this;
        const operators = [{
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
        }];

        me.data = [];

        Ext.iterate(operators, (operator) => {
            if (data.allowed.indexOf(operator.operator) === -1) {
                return true;
            }

            me.data.push(operator);
        });

        me.callParent(arguments);
    }
});