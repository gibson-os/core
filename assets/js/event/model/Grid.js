Ext.define('GibsonOS.module.core.event.model.Grid', {
    extend: 'GibsonOS.data.Model',
    fields: [{
        name: 'id',
        type: 'int'
    },{
        name: 'name',
        type: 'string'
    },{
        name: 'trigger',
        type: 'array'
    },{
        name: 'active',
        type: 'bool'
    },{
        name: 'async',
        type: 'bool'
    },{
        name: 'exitOnError',
        type: 'bool'
    },{
        name: 'lastRun',
        type: 'string'
    }]
});