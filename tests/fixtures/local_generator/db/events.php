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
 * This is a one-line short description of the file.
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    local_localgeneratortest
 * @copyright  2016 Alexandru Elisei alexandru.elisei@gmail.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// For more information about the Events API, please visit:
// https://docs.moodle.org/dev/Event_2

defined('MOODLE_INTERNAL') || die();

$observers = array(

    'eventname' => '\core\event\something_happened',
    'callback' => '\local_localgeneratortest\event_observer::something_happened',
    'priority' => 200,

    'eventname' => '\core\event\something_else_happened',
    'callback' => '\local_localgeneratortest\another_event_observer::something_else_happened',
);
