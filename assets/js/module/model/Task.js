Ext.define('GibsonOS.module.core.module.model.Task', {
    extend: 'GibsonOS.data.Model',
    fields: [{
        name: 'id',
        type: 'int'
    },{
        name: 'moduleId',
        type: 'int'
    },{
        name: 'name',
        type: 'string'
    }]
});