GibsonOS.define('GibsonOS.module.core.module.fn.scan', function(params) {
    GibsonOS.Ajax.request({
        url: baseDir + 'core/module/scan',
        method: 'POST',
        success: function(response, options) {
            GibsonOS.MessageBox.show({
                title: 'Erfolgreich!',
                msg: 'Module wurden erfolgreich gescannt!',
                type: GibsonOS.MessageBox.type.INFO
            });

            if (params.success) {
                params.success(response, options);
            }
        },
        failure: function(response, options) {
            if (params.failure) {
                params.failure(response, options);
            }
        }
    });
});