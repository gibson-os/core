Ext.define('GibsonOS.module.core.event.element.trigger.model.ComboBox', {
    extend: 'GibsonOS.data.Model',
    fields: [{
        name: 'trigger',
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