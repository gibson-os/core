Ext.define('GibsonOS.module.core.module.model.Setting', {
    extend: 'GibsonOS.data.Model',
    fields: [{
        name: 'user',
        type: 'string'
    },{
        name: 'user_id',
        type: 'int'
    },{
        name: 'key',
        type: 'string'
    },{
        name: 'value',
        type: 'string'
    }]
});