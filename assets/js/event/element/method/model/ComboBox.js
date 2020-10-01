Ext.define('GibsonOS.module.core.event.element.method.model.ComboBox', {
    extend: 'GibsonOS.data.Model',
    fields: [{
        name: 'method',
        type: 'string'
    },{
        name: 'title',
        type: 'string'
    },{
        name: 'parameters',
        type: 'object'
    },{
        name: 'returns',
        type: 'object'
    }]
});