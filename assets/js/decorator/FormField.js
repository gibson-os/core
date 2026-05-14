GibsonOS.define('GibsonOS.decorator.FormField', {
    addListeners: (component) => {
        console.log(component);
        if (typeof(component.submitOnChange) === 'object') {
            component.on('change', () => {
                component.up('form').submit({
                    xtype: 'gosFormActionAction',
                    url: baseDir + component.submitOnChange.module + '/' + component.submitOnChange.task + '/' + component.submitOnChange.action,
                    params: component.submitOnChange.parameters,
                    method: component.submitOnChange.method ?? 'POST'
                });
            });
        }
    }
});