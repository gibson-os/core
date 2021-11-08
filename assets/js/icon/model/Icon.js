Ext.define('GibsonOS.module.core.icon.model.Icon', {
    extend: 'GibsonOS.data.Model',
    fields: [{
        name: 'id',
        type: 'int'
    },{
        name: 'name',
        type: 'string'
    },{
        name: 'tags',
        type: 'array'
    }]
});