Ext.define('GibsonOS.module.core.module.model.Permission', {
    extend: 'GibsonOS.data.Model',
    fields: [{
        name: 'id',
        type: 'int'
    },{
        name: 'userName',
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