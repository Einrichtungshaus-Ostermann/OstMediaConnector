Ext.define('Shopware.apps.MediaConnector.model.MediaConnector', {
    extend: 'Shopware.data.Model',

    configure: function () {
        return {
            controller: 'MediaConnector'
        };
    },

    fields: [
        {
            name: 'name',
            type: 'string'
        },
        {
            name: 'configParameter',
            type: 'json'
        }
    ]
});

