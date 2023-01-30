Ext.define('GibsonOS.module.core.desktop.model.Item', {
    extend: 'GibsonOS.data.Model',
    fields: [{
        name: 'id',
        type: 'int'
    },{
        name: 'text',
        type: 'string'
    },{
        name: 'icon',
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
        name: 'position',
        type: 'int'
    },{
        name: 'parameters',
        type: 'object'
    }]
});