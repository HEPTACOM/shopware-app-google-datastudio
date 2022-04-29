<?xml version="1.0" encoding="UTF-8"?>
<manifest
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:noNamespaceSchemaLocation="https://raw.githubusercontent.com/shopware/platform/v6.4.0.0/src/Core/Framework/App/Manifest/Schema/manifest-1.0.xsd"
>
    <meta>
        <name>{{ config('heptaconnect-shopware-six.app_name') }}</name>
        <label>HEPTAconnect Cloud | Goolge Data Studio</label>
        <label lang="de-DE">HEPTAconnect Cloud | Goolge Data Studio</label>
        <description>HEPTAconnect Cloud connects Shopware 6 with Google Data Studio and enables you to have interactive dashboards and clear reports and to connect many other data sources.</description>
        <description lang="de-DE">HEPTAconnect Cloud verbindet Shopware 6 mit Google Data Studio und ermöglichen dir so interaktive Dashboards und übersichtliche Reports und das Verbinden vieler weiterer Datenquellen.</description>
        <author>HEPTACOM GmbH</author>
        <copyright>(c) by HEPTACOM GmbH</copyright>
        <version>{{ $version }}</version>
        <icon>icon.png</icon>
        <license>proprietary</license>
    </meta>
    <setup>
        <registrationUrl>{{ route('api.v1.shopware6.register') }}</registrationUrl>
        @if ($isDev)
            <secret>{{ config('heptaconnect-shopware-six.app_secret') }}</secret>
        @endif
    </setup>
    <permissions>
        <read>country</read>
        <read>customer</read>
        <read>customer_group</read>
        <read>language</read>
        <read>order</read>
        <read>order_address</read>
        <read>order_customer</read>
        <read>order_delivery</read>
        <read>order_transaction</read>
        <read>payment_method</read>
        <read>sales_channel</read>
        <read>state_machine_state</read>
    </permissions>
    <admin>
        <module
            source="{{ route('api.v1.shopware6.wizard') }}"
            name="setup-wizard"
        >
            <label>Setup</label>
            <label lang="de-DE">Einrichtung</label>
        </module>
        <main-module source="{{ route('api.v1.shopware6.wizard') }}"/>
    </admin>
    <webhooks>
        <webhook name="appLifecycleDeleted" url="{{ route('api.v1.shopware6.appLifecycle.deleted') }}" event="app.deleted"/>
    </webhooks>
</manifest>
