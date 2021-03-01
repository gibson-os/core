Ext.define('GibsonOS.module.core.weather.model.Location', {
    extend: 'GibsonOS.data.Model',
    fields: [{
        name: 'id',
        type: 'int'
    },{
        name: 'name',
        type: 'string'
    },{
        name: 'latitude',
        type: 'float'
    },{
        name: 'longitude',
        type: 'float'
    },{
        name: 'timezone',
        type: 'string'
    },{
        name: 'active',
        type: 'boolean'
    },{
        name: 'lastRun',
        type: 'date'
    }]
});