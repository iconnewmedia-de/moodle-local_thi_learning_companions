<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin capabilities are defined here.
 *
 * Das Projekt THISuccessAI (FBM202-EA-1690-07540) wird im Rahmen der Förderlinie „Hochschulen durch Digitalisierung stärken“
 * durch die Stiftung Innovation in der Hochschulehre gefördert.
 *
 * @package     local_thi_learning_companions
 * @category    access
 * @copyright   2022 ICON Vernetzte Kommunikation GmbH <spiros.tzanetatos@iconnewmedia.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = [

    'local/thi_learning_companions:group_create' => [
        'riskbitmask' => RISK_MANAGETRUST,
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'student' => CAP_ALLOW,
        ],
    ],
    'local/thi_learning_companions:group_manage' => [
        'riskbitmask' => RISK_MANAGETRUST,
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'manager' => CAP_ALLOW,
        ],
    ],
    'local/thi_learning_companions:mentor_view' => [
        'riskbitmask' => RISK_MANAGETRUST,
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'student' => CAP_ALLOW,
        ],
    ],
    'local/thi_learning_companions:mentor_search' => [
        'riskbitmask' => RISK_MANAGETRUST,
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'manager' => CAP_ALLOW,
        ],
    ],
    'local/thi_learning_companions:mentor_ismentor' => [
        'riskbitmask' => RISK_MANAGETRUST,
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
    ],
    'local/thi_learning_companions:mentor_issupermentor' => [
        'riskbitmask' => RISK_MANAGETRUST,
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
    ],
    'local/thi_learning_companions:mentor_istutor' => [
        'riskbitmask' => RISK_MANAGETRUST,
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
    ],
    'local/thi_learning_companions:group_view' => [
        'riskbitmask' => RISK_MANAGETRUST,
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'manager' => CAP_ALLOW,
        ],
    ],
    'local/thi_learning_companions:group_search' => [
        'riskbitmask' => RISK_MANAGETRUST,
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'manager' => CAP_ALLOW,
            'user' => CAP_ALLOW,
        ],
    ],
    'local/thi_learning_companions:delete_comments_of_others' => [
        'riskbitmask' => RISK_MANAGETRUST,
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'manager' => CAP_ALLOW,
        ],
    ],
    'local/thi_learning_companions:view_all_mentor_questions' => [
        'riskbitmask' => RISK_MANAGETRUST,
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'manager' => CAP_ALLOW,
        ],
    ],
];
