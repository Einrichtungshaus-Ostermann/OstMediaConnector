Ext.define('Shopware.apps.MediaConnector.model.MediaConnectorConfig', {
    extend: 'Shopware.data.Model',

    configure: function () {
        return {
            controller: 'MediaConnectorConfig',
            detail: 'Shopware.apps.MediaConnector.view.MediaConnectorConfig.detail.Container'
        };
    },

    fields: [
        {
            name: 'id',
            type: 'int',
            useNull: true
        },
        {
            name: 'priority',
            type: 'int'
        },
        {
            name: 'providerName',
            type: 'string'
        },
        {
            name: 'query',
            type: 'string'
        },
        {
            name: 'config',
            type: 'json',
            useNull: true
        }
    ]
});

