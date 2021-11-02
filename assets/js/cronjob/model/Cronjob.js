Ext.define('GibsonOS.module.core.cronjob.model.Cronjob', {
    extend: 'GibsonOS.data.Model',
    fields: [{
        name: 'id',
        type: 'int'
    },{
        name: 'command',
        type: 'string'
    },{
        name: 'user',
        type: 'string'
    },{
        name: 'arguments',
        type: 'string'
    },{
        name: 'options',
        type: 'string'
    },{
        name: 'last_run',
        type: 'string'
    },{
        name: 'active',
        type: 'bool'
    }]
});