Ext.define('GibsonOS.module.core.event.trigger.model.Grid', {
    extend: 'GibsonOS.data.Model',
    fields: [{
        name: 'trigger',
        type: 'string'
    },{
        name: 'weekday',
        type: 'int',
        useNull: true
    },{
        name: 'day',
        type: 'int',
        useNull: true
    },{
        name: 'month',
        type: 'int',
        useNull: true
    },{
        name: 'year',
        type: 'int',
        useNull: true
    },{
        name: 'hour',
        type: 'int',
        useNull: true
    },{
        name: 'minute',
        type: 'int',
        useNull: true
    },{
        name: 'second',
        type: 'int',
        useNull: true
    }]
});