Ext.define('GibsonOS.module.core.module.model.Action', {
    extend: 'GibsonOS.data.Model',
    fields: [{
        name: 'id',
        type: 'int'
    },{
        name: 'moduleId',
        type: 'int'
    },{
        name: 'taskId',
        type: 'int'
    },{
        name: 'name',
        type: 'string'
    }]
});