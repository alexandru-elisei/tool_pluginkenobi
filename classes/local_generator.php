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
 * File containing the local_generator class.
 *
 * @package    tool_pluginkenobi
 * @copyright  2016 Alexandru Elisei
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/classes/component.php');
require_once(__DIR__ . '/generator_base.php');
require_once(__DIR__ . '/processor.php');

/**
 * Local_generator class.
 *
 * @package    tool_pluginkenobi
 * @copyright  2016 Alexandru Elisei
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_pluginkenobi_local_generator extends tool_pluginkenobi_generator_base {
    /** @var $string[] List of features that the plugin has. */
    protected $features = array(
        'core'      => array(
            'requiredoptions'   => array(),
            'optionaloptions'   => array(),
            'files'             => array(),
        ),

        'settings'  => 'settings_generator',
        'capabilities' => 'capabilities_generator',
        'observers' => 'observers_generator',
        'events' => 'events_generator',
        'uninstall' => 'uninstall_generator',
        'install' => 'install_generator',
        'upgrade' => 'upgrade_generator'
    );

    /** @var string Default plugin location. */
    protected $defaultlocation = '/local';
}
