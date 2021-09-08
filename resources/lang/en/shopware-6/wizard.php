<?php

return [
    'header' => [
        'title' => 'HEPTAconnect Cloud',
        'subtitle' => 'Data visualization',
    ],
    'pages' => [
        'intro' => [
            'content' => <<<'RAW'
Welcome!

In this setup we connect Google Data Studio with Shopware.

We do not transfer any personal data from your shop!
RAW,
        ],
        'data-config' => [
            'content' => <<<'RAW'
This data is processed on our servers and summarized and transmitted to Google Data Studio:

- Shipping costs
- Shipping zip code
- Shipping country
- Billing zip code
- Billing country
- Customer number
- Customer affiliate
- Customer group
- Customer origin (referrer)
- Product number
- Product price
- Number of products
- Product manufacturer
- Subshop / sales channel
- Language
- Voucher number
- Voucher value
- Payment method
- Shipping method
- Order number
- Order value
- Time of order

In subsequent versions you can exclude individual data from the transfer.
RAW,
        ],
        'setup' => [
            'connect' => <<<'RAW'
Connect Google Data Studio with Shopware now.
RAW,
            'content' => <<<'RAW'
If you already have an account, you can go straight to the integration. If not, follow the instructions from Data Studio to create a free account.

Then enter this setup code:
RAW,
        ],
        'finish' => [
            'content' => <<<'RAW'
Setup completed!

Now follow the instructions from Google Data Studio.

If you have any questions take a look at our Youtube video:
RAW,
        ],
    ],
    'navigation' => [
        'next' => 'Next',
        'previous' => 'Back',
        'route' => [
            'data-config' => 'Show me which data is being transmitted',
            'youtube' => 'Go to YouTube',
            'google-data-studio' => 'Go to Google Data Studio',
        ],
    ],
];
