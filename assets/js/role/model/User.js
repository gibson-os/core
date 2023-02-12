Ext.define('GibsonOS.module.core.role.model.User', {
    extend: 'GibsonOS.data.Model',
    fields: [{
        name: 'id',
        type: 'int'
    },{
        name: 'roleId',
        type: 'int'
    },{
        name: 'userId',
        type: 'int',
        useNull: true
    },{
        name: 'userName',
        type: 'string'
    }]
});