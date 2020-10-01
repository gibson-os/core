Ext.define('GibsonOS.module.core.event.model.Grid', {
    extend: 'GibsonOS.data.Model',
    fields: [{
        name: 'name',
        type: 'string'
    },{
        name: 'trigger',
        type: 'array'
    },{
        name: 'active',
        type: 'bool'
    }]
});