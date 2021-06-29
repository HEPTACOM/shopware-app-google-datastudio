<?xml version="1.0" encoding="UTF-8"?>
<manifest
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:noNamespaceSchemaLocation="https://raw.githubusercontent.com/shopware/app-system/0.2.0/src/Core/Content/App/Manifest/Schema/manifest-1.0.xsd"
>
    <meta>
        <name>{{ config('heptaconnect-shopware-six.app_name') }}</name>
        <icon>https://www.heptacom.de/resources/static/google-datastudio-logo-heptaconnect.png</icon>
        <label>HEPTAconnect Cloud | Goolge Data Studio</label>
        <label lang="de-DE">HEPTAconnect Cloud | Goolge Data Studio</label>
        <description>HEPTAconnect Cloud connects Shopware 6 with Google Data Studio and enables you to have interactive dashboards and clear reports and to connect many other data sources.</description>
        <description lang="de-DE">HEPTAconnect Cloud verbindet Shopware 6 mit Google Data Studio und ermöglichen dir so interaktive Dashboards und übersichtliche Reports und das Verbinden vieler weiterer Datenquellen.</description>
        <author>HEPTACOM GmbH</author>
        <copyright>(c) by HEPTACOM GmbH</copyright>
        <version>0.2.0</version>
        <license>proprietary</license>
    </meta>
    <setup>
        <registrationUrl>{{ route('api.v1.shopware6.register') }}</registrationUrl>
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
</manifest>
