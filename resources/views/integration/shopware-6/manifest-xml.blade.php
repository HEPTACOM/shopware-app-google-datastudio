<?xml version="1.0" encoding="UTF-8"?>
<manifest
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:noNamespaceSchemaLocation="https://raw.githubusercontent.com/shopware/app-system/0.2.0/src/Core/Content/App/Manifest/Schema/manifest-1.0.xsd"
>
    <meta>
        <name>{{ config('heptaconnect-shopware-six.app_name') }}</name>
        <label>HEPTAconnect.cloud</label>
        <label lang="de-DE">HEPTAconnect.cloud</label>
        <description>A description</description>
        <description lang="de-DE">Eine Beschreibung</description>
        <author>HEPTACOM GmbH</author>
        <copyright>(c) by HEPTACOM GmbH</copyright>
        <version>0.1.0</version>
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
