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
 * File containing the version_generator class.
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
 * Version_generator class.
 *
 * @package    tool_pluginkenobi
 * @copyright  2016 Alexandru Elisei
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_pluginkenobi_version_generator extends tool_pluginkenobi_generator_base {
    /** @var string[] Moodle versions. */
    protected $moodleversions = array(
        '3.1' => '2016052300',
        '3.0' => '2015111600',
        '2.9' => '2015051100',
        '2.8' => '2014111000',
        '2.7' => '2014051200',
        '2.6' => '2013111800',
        '2.5' => '2013051400',
        '2.4' => '2012120300',
        '2.3' => '2012062500',
        '2.2' => '2011120500'
    );

    /** @var string[] Valid maturity levels. */
    protected $maturitylevels = array('MATURITY_ALPHA', 'MATURITY_BETA', 'MATURITY_RC', 'MATURITY_STABLE');

    /** @var $string[] List of features that the plugin has. */
    protected $features = array(
        'core'      => array(
            'requiredoptions'   => array('component', 'version', 'requires'),
            'optionaloptions'   => array('release', 'maturity', 'dependencies'),
            'files'             => array(
                'skel/version' => 'version.php'
            )),
    );

    /**
     * Checks if a given option is valid.
     *
     * @param string $option The option to be validated.
     * @return string | null The validated option value or null if it's not a valid value.
     */
    protected function validate_value($option, $value) {
        if ($option == 'requires') {
            if (!isset($this->moodleversions[$value])) {
                throw new moodle_exception('Unknown version number "' . $value . '"');
            }
            return $this->moodleversions[$value];
        }

        if ($option == 'maturity' && !in_array($value, $this->maturitylevels)) {
            throw new moodle_exception('Unknown maturity level "' . $value . '"');
        }

        return $value;
    }
}
