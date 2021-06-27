<?php

return [
    'header' => [
        'title' => 'HEPTAconnect Cloud',
        'subtitle' => 'Datenvisualisierung',
    ],
    'pages' => [
        'intro' => [
            'content' => <<<'RAW'
Herzlich Willkommen!

In diesem Setup verbinden wir Google Data Studio mit Shopware.

Wir übertragen keine personenbezogenen Daten aus deinem Shop!
RAW,
        ],
        'data-config' => [
            'content' => <<<'RAW'
Dies Daten werden auf unseren Servern verarbeitet und zusammengefasst an Google Data Studio übermittelt:

Versandkosten
Versand PLZ
Versandland
Rechnung PLZ
Rechnungsland
Kundennummer
Kundenaffiliate
Kundengruppe
Kundenherkunft (referrer)
Produktnummer
Produktpreis
Produktanzahl
Produkthersteller
Subshop/Verkaufskanal
Sprache
Gutscheinnummer
Gutscheinwert
Bezahlmethode
Versandmethode
Bestellnummer
Bestellwert
Bestellzeitpunkt

In Folgeversionen kann du einzelne Daten von der Übertragung ausschließen.
RAW,
        ],
        'setup' => [
            'connect' => <<<'RAW'
Verbinde Google Data Studio jetzt mit Shopware.
RAW,
            'content' => <<<'RAW'
Wenn du bereits einen Account hast kommst du direkt zur integration. Wenn nicht folge den Anweisungen vom Data Studio zur Erstellung eines kostenlosen Accounts.

Gebe dann diesen Setupcode ein:
RAW,
        ],
        'finish' => [
            'content' => <<<'RAW'
Setup abgeschlossen!

Folge nun den Anweisungen von Google Data Studio.

Wenn du fragen hast sieh dir unser Youtube Video an:
RAW,
        ],
    ],
    'navigation' => [
        'next' => 'Weiter',
        'previous' => 'Zurück',
        'route' => [
            'data-config' => 'Zeige mir welche Daten übermittelt werden',
            'youtube' => 'Zu YouTube',
            'google-data-studio' => 'Zu Google Data Studio',
        ],
    ],
];
