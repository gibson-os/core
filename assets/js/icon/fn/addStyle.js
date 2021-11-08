GibsonOS.define('GibsonOS.module.core.icon.fn.addStyle', function(data) {
    var style = document.createElement('style');
    style.setAttribute('type', 'text/css');
    style.innerHTML = '.customIcon' + data.id + ' {background-image: url(' + baseDir + 'img/icons/custom/icon' + data.id + '.png) !important;}';
    document.getElementsByTagName('head')[0].appendChild(style);
});