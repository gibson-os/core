GibsonOS.define('GibsonOS.decorator.action.Filter', {
    init: (component) => {
        component = Ext.merge(component, Ext.merge({
            filterFunction: null,
            filterMenu: {
                text: 'Filter',
                itemId: 'filterMenu',
                iconCls: 'icon_system system_filter',
                hidden: true,
                menu: [],
            },
            setFilters(filters) {
                if (typeof (component.filterFunction) !== 'function') {
                    return;
                }

                const filterMenu = this.down('#filterMenu');

                if (Object.keys(filters ?? {}).length === 0) {
                    filterMenu.disable();

                    return;
                }

                filterMenu.show();
                filterMenu.enable();

                const collectFilters = () => {
                    let filters = {};

                    filterMenu.menu.items.each((filter) => {
                        let options = [];

                        filter.menu.items.each((option) => {
                            if (option.itemId === 'all' && option.checked) {
                                options = null;

                                return false;
                            }

                            if (option.value !== undefined && option.checked) {
                                options.push(option.value);
                            }
                        });

                        if (options !== null) {
                            filters[filter.value] = options;
                        }
                    });

                    return filters;
                }

                Ext.iterate(filters, (filterKey, filter) => {
                    const menuItemId = 'filter' + filterKey;
                    let filterValueMenu = filterMenu.down('#' + menuItemId);
                    let isNew = false;

                    if (filterValueMenu === null) {
                        isNew = true;
                        filterValueMenu = filterMenu.menu.add({
                            text: filter.name,
                            itemId: menuItemId,
                            value: filterKey,
                            menu: [{
                                xtype: 'menucheckitem',
                                itemId: 'all',
                                text: 'Alle',
                                checked: true,
                                checkHandler(item, checked) {
                                    item.up().items.each((option) => {
                                        if (option.value !== undefined) {
                                            option.setChecked(checked, true);
                                        }
                                    });

                                    component.filterFunction(collectFilters());
                                }
                            },('-')]
                        });
                    }

                    Ext.iterate(filter.options, (option, index) => {
                        const filterItem = filterValueMenu.menu.items.findBy((item) => {
                            return item.value === option.value
                        });

                        if (filterItem !== null) {
                            return true;
                        }

                        filterValueMenu.menu.insert(index+2, {
                            xtype: 'menucheckitem',
                            text: option.name === '' ? '&nbsp;' : option.name,
                            value: option.value,
                            checked: isNew,
                            checkHandler() {
                                let allChecked = true;

                                filterValueMenu.menu.items.each((option) => {
                                    if (option.value !== undefined) {
                                        allChecked = option.checked;
                                    }

                                    return allChecked;
                                });
                                filterValueMenu.down('#all').setChecked(allChecked, true);

                                component.filterFunction(collectFilters());
                            }
                        })
                    });
                });
            }
        }, component));

        component.addAction(component.filterMenu);

        return component;
    }
});