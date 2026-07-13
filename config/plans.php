<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default plan
    |--------------------------------------------------------------------------
    |
    | Plan assigned to newly registered tenants. New tenants also receive a
    | trial period (see 'trial_days') during which limits are relaxed to the
    | trial plan.
    |
    */

    'default' => 'free',

    'trial_plan' => 'pro',

    'trial_days' => 14,

    /*
    |--------------------------------------------------------------------------
    | Plans
    |--------------------------------------------------------------------------
    |
    | A null limit means "unlimited". Payment/billing integration is TODO;
    | plans are currently switched manually or via the trial.
    |
    */

    'plans' => [
        'free' => [
            'name' => 'Ücretsiz',
            'price' => 0,
            'limits' => [
                'customers' => 25,
                'users' => 1,
                'sms_per_month' => 50,
                'mail_per_month' => 200,
            ],
        ],

        'pro' => [
            'name' => 'Pro',
            'price' => 499,
            'limits' => [
                'customers' => 500,
                'users' => 5,
                'sms_per_month' => 2000,
                'mail_per_month' => null,
            ],
        ],

        'business' => [
            'name' => 'Business',
            'price' => 1499,
            'limits' => [
                'customers' => null,
                'users' => null,
                'sms_per_month' => null,
                'mail_per_month' => null,
            ],
        ],
    ],

];
