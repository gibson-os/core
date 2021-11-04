Ext.define('GibsonOS.module.core.module.model.Permission', {
    extend: 'GibsonOS.data.Model',
    fields: [{
        name: 'user',
        type: 'string'
    },{
        name: 'host',
        type: 'string'
    },{
        name: 'ip',
        type: 'string'
    },{
        name: 'module',
        type: 'string'
    },{
        name: 'task',
        type: 'string'
    },{
        name: 'action',
        type: 'string'
    },{
        name: 'user_id',
        type: 'int'
    },{
        name: 'permission',
        type: 'int'
    },{
        name: 'parent_permission',
        type: 'int'
    }]
});