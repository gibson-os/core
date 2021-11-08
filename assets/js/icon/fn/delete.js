GibsonOS.define('GibsonOS.module.core.icon.fn.delete', function(records, success) {
    var title = 'Icons löschen?';
    var msg = 'Möchten Sie die ' + records.length + ' Icons wirklich löchen?';

    if (records.length == 1) {
        title = 'Icon löschen?';
        msg = 'Möchten Sie das Icon ' + records[0].get('name') + ' wirklich löchen?';
    }

    GibsonOS.MessageBox.show({
        title: title,
        msg: msg,
        type: GibsonOS.MessageBox.type.QUESTION,
        buttons: [{
            text: 'Ja',
            handler: function() {
                var ids = [];

                Ext.iterate(records, function(record) {
                    ids.push(record.get('id'));
                });

                GibsonOS.Ajax.request({
                    url: baseDir + 'core/icon/delete',
                    params: {
                        ids: Ext.encode(ids)
                    },
                    success: function(response) {
                        if (success) {
                            success(response);
                        }
                    }
                });
            }
        },{
            text: 'Nein'
        }]
    });
});