Ext.define('GibsonOS.module.core.module.model.role.Permission', {
    extend: 'GibsonOS.data.Model',
    fields: [{
        name: 'id',
        type: 'int'
    },{
        name: 'roleName',
        type: 'string'
    },{
        name: 'moduleName',
        type: 'string'
    },{
        name: 'taskName',
        type: 'string'
    },{
        name: 'actionName',
        type: 'string'
    },{
        name: 'roleId',
        type: 'int'
    },{
        name: 'permission',
        type: 'int'
    },{
        name: 'parentPermission',
        type: 'int'
    }]
});