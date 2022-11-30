<?php

defined('MOODLE_INTERNAL') || die();
$messageproviders = [
    'group_join_requested' => [
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            'email' => MESSAGE_PERMITTED
        ],
    ]
];
