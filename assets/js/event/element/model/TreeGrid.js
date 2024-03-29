Ext.define('GibsonOS.module.core.event.element.model.TreeGrid', {
    extend: 'GibsonOS.data.Model',
    fields: [{
        name: 'id',
        type: 'int'
    },{
        name: 'command',
        type: 'string'
    },{
        name: 'className',
        type: 'string'
    },{
        name: 'classNameTitle',
        type: 'string'
    },{
        name: 'method',
        type: 'string'
    },{
        name: 'methodTitle',
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