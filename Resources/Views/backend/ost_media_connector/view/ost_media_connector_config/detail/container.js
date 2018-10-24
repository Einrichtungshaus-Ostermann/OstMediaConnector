Ext.define('Shopware.apps.OstMediaConnector.view.OstMediaConnectorConfig.detail.Container', {
    extend: 'Shopware.model.Container',
    padding: 20,

    configure: function () {
        var me = this;
        var mediaProviderStore = Ext.create('Shopware.apps.OstMediaConnector.store.MediaProvider');

        function createFormField(name, type) {
            var field = {
                name: name,
                type: type
            }, formField = {};


            //add default configuration for a form field.
            formField.xtype = 'displayfield';
            formField.anchor = '100%';
            formField.margin = '0 3 7 0';
            formField.labelWidth = 130;
            formField.name = field.name;

            //convert the model field name to a human readable word
            formField.fieldLabel = me.getHumanReadableWord(field.name);

            switch (field.type) {
                case 'int':
                    formField = me.applyIntegerFieldConfig(formField);
                    break;
                case 'string':
                    formField = me.applyStringFieldConfig(formField);
                    break;
                case 'bool':
                    formField = me.applyBooleanFieldConfig(formField);
                    break;
                case 'date':
                    formField = me.applyDateFieldConfig(formField);
                    break;
                case 'float':
                    formField = me.applyFloatFieldConfig(formField);
                    break;
            }

            formField = Ext.apply(formField);

            return formField;
        }

        me.configFieldSets = {};
        me.configValues = {};
        me.selected = null;

        mediaProviderStore.on('load', function (result) {
            result.each(function (element) {
                var name = element.get('name');
                var fields = [];

                me.configValues[name] = {};

                element.get('configParameter').forEach(function (configParameter) {
                    fields.push(createFormField(configParameter.name, configParameter.type));
                });

                if (!fields.length) {
                    return;
                }

                var fieldSet = Ext.create('Ext.form.FieldSet', {
                    flex: 1,
                    padding: '10 20',
                    layout: 'column',
                    items: fields,
                    title: name
                });


                fieldSet.items.items.forEach(function (item) {
                    item.on('change', function (changedItem, newValue, oldValue) {
                        console.log(me.configValues);
                        me.configValues[name][item.name] = newValue;
                    });
                });

                me.configFieldSets[name] = fieldSet;


                Object.values(me.configFieldSets).forEach(function (fieldSet) {
                    fieldSet.hide();
                    me.add(fieldSet);
                });

                // console.log(result);
                // me.configValues[configParameter.name] = 'bla'; //TODO: Load current Parameter
            });
        });

        return {
            controller: 'OstMediaConnectorConfig',
            fieldSets: [
                {
                    title: 'Media Provider',
                    fields: {
                        priority: {},
                        providerName: {
                            xtype: 'combo',
                            queryMode: 'local',
                            store: mediaProviderStore,
                            valueField: 'name',
                            displayField: 'name',
                            listeners: {
                                change: function (element, newValue, oldValue) {
                                    if (me.configFieldSets[newValue] !== undefined) {
                                        me.configFieldSets[newValue].show();
                                        me.selected = newValue;
                                    }

                                    if (me.configFieldSets[oldValue] !== undefined) {
                                        me.configFieldSets[oldValue].hide();
                                    }
                                }
                            }
                        },
                        query: {},
                        config: {}
                    }
                }
            ]
        };
    }

});