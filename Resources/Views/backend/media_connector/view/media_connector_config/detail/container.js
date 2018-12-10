Ext.define('Shopware.apps.MediaConnector.view.MediaConnectorConfig.detail.Container', {
    extend: 'Shopware.model.Container',
    padding: 20,

    configure: function () {
        var me = this;
        var mediaProviderStore = Ext.create('Shopware.apps.MediaConnector.store.MediaConnector');
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
        me.selected = null;

        mediaProviderStore.on('load', function (result) {
            // Hide Config field
            me.items.items[0].items.items[1].items.items[1].hide();

            result.each(function (element) {
                var name = element.get('name');
                var fields = [];


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
                    if (name === me.initialConfig.record.data["providerName"] && me.initialConfig.record.data["config"]) {
                        var config = JSON.parse(me.initialConfig.record.data["config"]);

                        item.setValue(config[item.name]);
                    }
                });

                me.configFieldSets[name] = fieldSet;


                Object.values(me.configFieldSets).forEach(function (fieldSet) {
                    if (name !== me.initialConfig.record.data["providerName"]) {
                        fieldSet.hide();
                    }

                    me.add(fieldSet);
                });
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
