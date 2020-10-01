Ext.define('GibsonOS.module.core.event.element.model.TreeGrid', {
    extend: 'GibsonOS.data.Model',
    fields: [{
        name: 'command',
        type: 'string'
    },{
        name: 'className',
        type: 'string'
    },{
        name: 'method',
        type: 'string'
    },{
        name: 'parameters',
        type: 'object'
    },{
        name: 'hasParameters',
        type: 'boolean'
    },{
        name: 'operator',
        type: 'string'
    },{
        name: 'returns',
        type: 'object'
    },{
        name: 'hasReturn',
        type: 'boolean'
    }]
});