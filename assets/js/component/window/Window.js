Ext.define('GibsonOS.module.core.window.Window', {
    extend: 'Ext.window.Window',
    alias: ['widget.gosWindow'],
    title: 'Window',
    border: false,
    frame: false,
    shadow: false,
    dirty: false,
    layout: 'fit',
    buttonAlign: 'center',
    renderTo: 'extBody',
    checkLogin: true,
    autoShow: true,
    defaults: {
        xtype: 'gosCoreComponentPanel'
    },
    setDirty(dirty) {
        this.dirty = dirty;
    },
    isDirty() {
        return this.dirty;
    },
    initComponent() {
        const me = this;

        me.callParent();

        let permissions = [];
        let permissionsAjax = {};

        const getItemPermissions = (element, permission) => {
            if (!element) {
                return false;
            }

            if (!Ext.isObject(permission)) {
                permission = {
                    module: null,
                    task: null,
                    action: null,
                    method: null,
                    permission: permission
                };
            }

            if (Ext.isObject(element.requiredPermission)) {
                permission = {
                    element: element,
                    module: element.requiredPermission.module ? element.requiredPermission.module : permission.module,
                    task: element.requiredPermission.task ? element.requiredPermission.task : permission.task,
                    action: element.requiredPermission.action ? element.requiredPermission.action : permission.action,
                    method: element.requiredPermission.method ? element.requiredPermission.method : permission.method,
                    permission: element.requiredPermission.permission ? element.requiredPermission.permission : permission.permission
                };

                if (permission.permission > GibsonOS.Permission.DENIED) {
                    permissions.push(permission);

                    if (!Ext.isObject(permissionsAjax[permission.module])) {
                        permissionsAjax[permission.module] = {
                            permissionRequired: false,
                            items: {}
                        };
                    }

                    if (permission.task) {
                        if (!Ext.isObject(permissionsAjax[permission.module].items[permission.task])) {
                            permissionsAjax[permission.module].items[permission.task] = {
                                permissionRequired: false,
                                items: {}
                            };
                        }

                        if (permission.action) {
                            permissionsAjax[permission.module].items[permission.task].items[permission.action] = {
                                permissionRequired: true,
                                method: permission.method
                            }
                        } else {
                            permissionsAjax[permission.module].items[permission.task].permissionRequired = true;
                        }
                    } else {
                        permissionsAjax[permission.module].permissionRequired = true;
                    }
                }
            }

            if (
                element.items &&
                element.items.items
            ) {
                Ext.iterate(element.items.items, (item) => {
                    getItemPermissions(item, permission);
                });
            }

            if (
                element.dockedItems &&
                element.dockedItems.items
            ) {
                Ext.iterate(element.dockedItems.items, (item) => {
                    getItemPermissions(item, permission);
                });
            }

            if (
                element.menu &&
                element.menu.items &&
                element.menu.items.items
            ) {
                Ext.iterate(element.menu.items.items, (item) => {
                    getItemPermissions(item, permission);
                });
            }

            if (
                element.itemContextMenu &&
                element.itemContextMenu.items &&
                element.itemContextMenu.items.items
            ) {
                Ext.iterate(element.itemContextMenu.items.items, (item) => {
                    getItemPermissions(item, permission);
                });
            }

            if (
                element.containerContextMenu &&
                element.containerContextMenu.items &&
                element.containerContextMenu.items.items
            ) {
                Ext.iterate(element.containerContextMenu.items.items, (item) => {
                    getItemPermissions(item, permission);
                });
            }
        };
        getItemPermissions(this);

        const getPermission = (permission, permissions) => {
            if (!permission.action) {
                if (!permission.task) {
                    return permissions[permission.module].permission;
                }

                return permissions[permission.module].items[permission.task].permission;
            }

            return permissions[permission.module].items[permission.task].items[permission.action][permission.method].permission;
        };

        GibsonOS.Ajax.request({
            url: baseDir + 'core/setting/window',
            method: 'GET',
            withoutFailure: true,
            params: {
                id: this.getId(),
                requiredPermissions: Ext.encode(permissionsAjax)
            },
            success(response) {
                const data = Ext.decode(response.responseText).data;

                if (data.permissions) {
                    Ext.iterate(permissions, function(permission) {
                        if ((getPermission(permission, data.permissions) & permission.permission) === permission.permission) {
                            return true;
                        }

                        permission.element.disable();
                        permission.element.suspendEvents();
                        permission.element.enable = function() {};
                    });
                }

                if (data.settings) {
                    if (data.settings.height) {
                        this.setHeight(data.settings.height);
                    }

                    if (data.settings.width) {
                        this.setWidth(data.settings.width);
                    }

                    if (
                        data.settings.top &&
                        data.settings.left
                    ) {
                        this.setPosition(data.settings.top, data.settings.left);
                    }
                }
            }
        });
    }
});