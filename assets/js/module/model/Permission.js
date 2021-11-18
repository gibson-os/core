Ext.define('GibsonOS.module.core.module.model.Permission', {
    extend: 'GibsonOS.data.Model',
    fields: [{
        name: 'userName',
        type: 'string'
    },{
        name: 'userHost',
        type: 'string'
    },{
        name: 'userIp',
        type: 'string'
    },{
        name: 'module',
        type: 'string'
    },{
        name: 'task',
        type: 'string'
    },{
        name: 'action',
        type: 'string'
    },{
        name: 'userId',
        type: 'int'
    },{
        name: 'permission',
        type: 'int'
    },{
        name: 'parentPermission',
        type: 'int'
    }]
});