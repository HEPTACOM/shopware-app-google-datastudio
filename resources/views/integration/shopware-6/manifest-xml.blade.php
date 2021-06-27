<?xml version="1.0" encoding="UTF-8"?>
<manifest
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:noNamespaceSchemaLocation="https://raw.githubusercontent.com/shopware/app-system/0.2.0/src/Core/Content/App/Manifest/Schema/manifest-1.0.xsd"
>
    <meta>
        <name>HeptacomHeptaconnectCloud</name>
        <label>HEPTAconnect.cloud</label>
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
        <read>order</read>
    </permissions>
</manifest>
