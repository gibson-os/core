Ext.define('GibsonOS.module.core.event.trigger.model.Grid', {
    extend: 'GibsonOS.data.Model',
    fields: [{
        name: 'id',
        type: 'int'
    },{
        name: 'className',
        type: 'string'
    },{
        name: 'classNameTitle',
        type: 'string'
    },{
        name: 'trigger',
        type: 'string'
    },{
        name: 'triggerTitle',
        type: 'string'
    },{
        name: 'parameters',
        type: 'object'
    },{
        name: 'hasParameters',
        type: 'boolean'
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