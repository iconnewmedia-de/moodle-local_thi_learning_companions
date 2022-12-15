<?php
$functions = [
    'local_learningcompanions_nugget_list' => [
        'classname'    => 'local_learningcompanions\external',
        'methodname'   => 'list_nuggets',
        'classpath'    => 'local/learningcompanions/classes/external.php',
        'description'  => 'Load a list of a courses.',
        'type'         => 'read',
        'capabilities' => '',
        'ajax'         => true,
    ],
    'local_learningcompanions_get_invitable_users' => [
        'classname'    => 'local_learningcompanions\external',
        'methodname'   => 'get_invitable_users',
        'classpath'    => 'local/learningcompanions/classes/external.php',
        'description'  => 'Search for a user to invite.',
        'type'         => 'read',
        'capabilities' => '',
        'ajax'         => true,
    ],
    'local_learningcompanions_invite_user' => [
        'classname'    => 'local_learningcompanions\external',
        'methodname'   => 'invite_user',
        'classpath'    => 'local/learningcompanions/classes/external.php',
        'description'  => 'Invite a user to a group.',
        'type'         => 'write',
        'capabilities' => '',
        'ajax'         => true,
    ],
];
