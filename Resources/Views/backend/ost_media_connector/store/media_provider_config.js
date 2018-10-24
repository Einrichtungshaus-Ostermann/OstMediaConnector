Ext.define('Shopware.apps.OstMediaConnector.store.OstMediaConnectorConfig', {
    extend:'Shopware.store.Listing',

    configure: function() {
        return {
            controller: 'OstMediaConnectorConfig'
        };
    },
    model: 'Shopware.apps.OstMediaConnector.model.OstMediaConnectorConfig'
});