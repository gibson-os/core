GibsonOS.define('GibsonOS.module.core.icon.fn.actionComplete', function(formWindow, panel, record) {
    formWindow.down('form').getForm().on('actioncomplete', function(form, action, options) {
        panel.down('#coreIconTagGrid').getStore().loadData(action.result.tags);

        if (!record) {
            var data = action.result.data;
            panel.down('#coreIconView').getStore().add(data);
        }

        formWindow.close();
    });
});