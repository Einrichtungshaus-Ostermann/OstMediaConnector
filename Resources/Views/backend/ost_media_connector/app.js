Ext.define('Shopware.apps.OstMediaConnector', {
    extend: 'Enlight.app.SubApplication',

    name: 'Shopware.apps.OstMediaConnector',

    loadPath: '{url action=load}',
    bulkLoad: true,

    controllers: ['Main'],

    views: [
        'OstMediaConnectorConfig.list.Window',
        'OstMediaConnectorConfig.list.List',

        'OstMediaConnectorConfig.detail.Container',
        'OstMediaConnectorConfig.detail.Window'
    ],

    models: ['OstMediaConnectorConfig', 'MediaProvider'],
    stores: ['OstMediaConnectorConfig', 'MediaProvider'],

    launch: function () {
        return this.getController('Main').mainWindow;
    }
});