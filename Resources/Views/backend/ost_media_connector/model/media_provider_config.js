Ext.define('Shopware.apps.OstMediaConnector.model.OstMediaConnectorConfig', {
    extend: 'Shopware.data.Model',

    configure: function () {
        return {
            controller: 'OstMediaConnectorConfig',
            detail: 'Shopware.apps.OstMediaConnector.view.OstMediaConnectorConfig.detail.Container'
        };
    },

    fields: [
        {name: 'id', type: 'int', useNull: true},
        {name: 'priority', type: 'int'},
        {name: 'providerName', type: 'string'},
        {name: 'query', type: 'string'},
        {name: 'config', type: 'json'}
    ]
});

