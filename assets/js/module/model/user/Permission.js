Ext.define('GibsonOS.module.core.module.model.user.Permission', {
    extend: 'GibsonOS.data.Model',
    fields: [{
        name: 'id',
        type: 'int'
    },{
        name: 'userName',
        type: 'string'
    },{
        name: 'moduleId',
        type: 'int'
    },{
        name: 'moduleName',
        type: 'string'
    },{
        name: 'taskId',
        type: 'int'
    },{
        name: 'taskName',
        type: 'string'
    },{
        name: 'actionId',
        type: 'int'
    },{
        name: 'actionName',
        type: 'string'
    },{
        name: 'method',
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