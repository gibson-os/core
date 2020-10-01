Ext.define('GibsonOS.module.core.cronjob.model.Time', {
    extend: 'GibsonOS.data.Model',
    fields: [{
        name: 'id',
        type: 'int'
    },{
        name: 'cronjob_id',
        type: 'int'
    },{
        name: 'hour',
        type: 'string'
    },{
        name: 'minute',
        type: 'string'
    },{
        name: 'second',
        type: 'string'
    },{
        name: 'day_of_month',
        type: 'string'
    },{
        name: 'day_of_week',
        type: 'string'
    },{
        name: 'month',
        type: 'string'
    },{
        name: 'year',
        type: 'string'
    }]
});