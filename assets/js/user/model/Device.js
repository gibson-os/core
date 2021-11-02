Ext.define('GibsonOS.module.system.user.model.Device', {
    extend: 'GibsonOS.data.Model',
    fields: [{
        name: 'id',
        type: 'string'
    },{
        name: 'model',
        type: 'string'
    },{
        name: 'registration_id',
        type: 'string'
    }]
});