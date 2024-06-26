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

/**
 * Das Projekt THISuccessAI (FBM202-EA-1690-07540) wird im Rahmen der Förderlinie „Hochschulen durch Digitalisierung stärken“
 * durch die Stiftung Innovation in der Hochschulehre gefördert.
 *
 * @package     local_thi_learning_companions
 * @copyright   2022 ICON Vernetzte Kommunikation GmbH <info@iconnewmedia.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname' => '\core\event\course_module_created',
        'callback' => '\local_thi_learning_companions\eventobservers::course_module_created',
        'internal' => false,
    ],
    [
        'eventname' => '\core\event\course_restored',
        'callback' => '\local_thi_learning_companions\eventobservers::course_restored',
        'internal' => false,
    ],
    [
        'eventname'   => '\core\event\badge_awarded',
        'callback'    => '\local_thi_learning_companions\eventobservers::badge_awarded',
    ],
    [
        'eventname'   => '\core\event\config_log_created',
        'callback'    => '\local_thi_learning_companions\eventobservers::config_log_created',
    ],
];
