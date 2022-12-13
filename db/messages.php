<?php

defined('MOODLE_INTERNAL') || die();

$messageproviders = [
    'group_join_requested' => [
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            'email' => MESSAGE_PERMITTED
        ],
    ],
    'appointed_to_admin' => [
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            'email' => MESSAGE_PERMITTED
        ],
    ],
    'group_join_accepted' => [
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            'email' => MESSAGE_PERMITTED
        ],
    ],
    'group_join_denied' => [
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            'email' => MESSAGE_PERMITTED
        ],
    ],

];
