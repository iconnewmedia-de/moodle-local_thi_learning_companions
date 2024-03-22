<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
defined('MOODLE_INTERNAL') || die();
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
