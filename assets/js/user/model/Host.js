Ext.define('GibsonOS.module.system.user.model.Host', {
    extend: 'GibsonOS.data.Model',
    fields: [{
        name: 'id',
        type: 'int'
    },{
        name: 'host',
        type: 'string'
    },{
        name: 'ip',
        type: 'string'
    }]
});