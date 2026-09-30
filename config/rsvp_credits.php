<?php

return [
    'packs' => [
        'slots_100'  => ['label' => '100 Extra Attendees',      'slots' => 100,  'emails' => 100,  'sms' => 0,   'price_ghs' => 45.00],
        'slots_500'  => ['label' => '500 Extra Attendees',      'slots' => 500,  'emails' => 500,  'sms' => 0,   'price_ghs' => 150.00],
        'slots_1000' => ['label' => '1000 Extra Attendees',     'slots' => 1000, 'emails' => 1000, 'sms' => 0,   'price_ghs' => 250.00],
        'sms_50'     => ['label' => '50 SMS Notifications',     'slots' => 0,    'emails' => 0,    'sms' => 50,  'price_ghs' => 20.00],
        'sms_200'    => ['label' => '200 SMS Notifications',    'slots' => 0,    'emails' => 0,    'sms' => 200, 'price_ghs' => 65.00],
        'bundle_100' => ['label' => '100 Attendees + 100 SMS',  'slots' => 100,  'emails' => 100,  'sms' => 100, 'price_ghs' => 75.00],
        'bundle_500' => ['label' => '500 Attendees + 200 SMS',  'slots' => 500,  'emails' => 500,  'sms' => 200, 'price_ghs' => 190.00],
    ],
    'min_quantity' => 1,
    'max_quantity' => 20,
];
