<?php
$functions = [
    'local_thi_learning_companions_nugget_list' => [
        'classname'    => 'local_thi_learning_companions\external',
        'methodname'   => 'list_nuggets',
        'classpath'    => 'local/thi_learning_companions/classes/external.php',
        'description'  => 'Load a list of a courses.',
        'type'         => 'read',
        'capabilities' => '',
        'ajax'         => true,
    ],
    'local_thi_learning_companions_get_invitable_users' => [
        'classname'    => 'local_thi_learning_companions\external',
        'methodname'   => 'get_invitable_users',
        'classpath'    => 'local/thi_learning_companions/classes/external.php',
        'description'  => 'Search for a user to invite.',
        'type'         => 'read',
        'capabilities' => '',
        'ajax'         => true,
    ],
    'local_thi_learning_companions_invite_user' => [
        'classname'    => 'local_thi_learning_companions\external',
        'methodname'   => 'invite_user',
        'classpath'    => 'local/thi_learning_companions/classes/external.php',
        'description'  => 'Invite a user to a group.',
        'type'         => 'write',
        'capabilities' => '',
        'ajax'         => true,
    ],
];
