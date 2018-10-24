Ext.define('Shopware.apps.OstMediaConnector.store.MediaProvider', {
    alternateClassName: 'OstMediaConnector.store.MediaProvider',
    extend: 'Ext.data.Store',
    storeId: 'OstMediaConnector.MediaProvider',
    autoLoad: true,
    remoteSort: false,
    remoteFilter: false,
    model: 'Shopware.apps.OstMediaConnector.model.MediaProvider',
    proxy: {
        type: 'ajax',
        url: '{url controller="OstMediaConnector" action="mediaProviderList"}',
        reader: {
            type: 'json',
            root: 'data',
            totalProperty: 'total'
        }
    }
});