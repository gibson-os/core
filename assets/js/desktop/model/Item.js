Ext.define('GibsonOS.module.core.desktop.model.Item', {
    extend: 'GibsonOS.data.Model',
    fields: [{
        name: 'text',
        type: 'string'
    },{
        name: 'icon',
        type: 'string'
    },{
        name: 'thumb',
        type: 'string'
    },{
        name: 'customIcon',
        type: 'int'
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
        name: 'params',
        type: 'object'
    }]
});