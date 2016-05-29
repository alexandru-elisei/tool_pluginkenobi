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
 * File containing the example_generator class.
 *
 * @package    tool_pluginkenobi
 * @copyright  2016 Alexandru Elisei
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/template_processor.php');
require_once(__DIR__ . '/processor.php');

/**
 * Example_generator class.
 *
 * @package    tool_pluginkenobi
 * @copyright  2016 Alexandru Elisei
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_pluginkenobi_example_generator {
    /** @var string[] Options for the generation of the plugin. */
    protected $options = array();

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

    /** @var string[] Required options for the generation of the plugin. */
    protected $requiredoptions = array('component', 'version', 'requires');

    /** @var string[] Optional options for the generation of the plugin. */
    protected $optionaloptions = array('release', 'maturity', 'dependencies');

    /** @var string The default location of the generated plugin relative to the Moodle directory. */
    protected $defaultplugindirectory = '/admin/tool/';

    /** @var string The directory where the templates reside. */
    protected $templatedirectory = 'skel/example/';

    /** @var string[] The templates used for generating the plugin. */
    protected $templatefiles = array(
        'example' => 'example.php'
    );

    /** @var bool Is the settings feature enabled? */
    protected $usesettings = false;

    /** @var string Directory where the plugin files will be generated. */
    protected $targetdir;

    /**
     * Class constructor.
     *
     * @throws moodle_exception.
     * @param string[] $options Generator options.
     */
    public function __construct($options, $targetdir) {
        global $CFG;

        // Adding the boilerplate variables.
        foreach (tool_pluginkenobi_processor::$boilerplateoptions as $option) {
            $this->options[$option] = $options[$option];
        }

        foreach ($this->requiredoptions as $option) {
            if (empty($options[$option])) {
                throw new moodle_exception('Required option "' . $option . '" missing');
            }

            $value = $this->validate_option($option, $options[$option]);
            if (is_null($value)) {
                throw new moodle_exception('Invalid value "' . $options[$option] . '" for option "' . $option . '"');
            }
            $this->options[$option] = $value;
        }

        foreach ($this->optionaloptions as $option) {
            if (!empty($options[$option])) {
                $value = $this->validate_option($option, $options[$option]);
                if (is_null($value)) {
                    throw new moodle_exception('Invalid value "' . $options[$option] . '" for option "' . $option . '"');
                }
                $this->options[$option] = $value;
            }
        }

        if (isset($options['features']) && is_array($options['features']) && in_array('settings', $options['features'])) {
            $this->usesettings = true;
            $this->templatefiles['settings'] = 'settings.php';
        }

        if (empty($targetdir)) {
            $this->targetdir = $CFG->dirroot . $this->defaultplugindirectory . $this->options['name'] . '/';
        } else {
            $this->targetdir = $targetdir . $this->options['name'] . '/';
        }
    }

    public function generate() {
        global $CFG;

        $result = mkdir($this->targetdir, 0755, true);
        if ($result === false) {
            throw new moodle_exception('Cannot create directory "' . $this->targetdir . '"');
        }

        $templatedirectory = $CFG->dirroot . '/admin/tool/pluginkenobi/' . $this->templatedirectory;
        foreach ($this->templatefiles as $templatename => $outputfile) {
            $contents = tool_pluginkenobi_template_processor::generate($templatename, $templatedirectory, $this->options);
            $outputfile = $this->targetdir . $outputfile;
            $handle = fopen($outputfile, 'w');
            fputs($handle, $contents);
            fclose($handle);
        }
    }

    public function get_target_directory() {
        return $this->targetdir;
    }

    /**
     * Checks if a given option is valid.
     *
     * @param string $option The option to be validated.
     * @return string | null The validated option value or null if it's not a valid value.
     */
    protected function validate_option($option, $value) {
        if ($option == 'requires') {
            if (!isset($this->moodleversions[$value])) {
                return null;
            }
            return $this->moodleversions[$value];
        }

        if ($option == 'maturity') {
            if (!in_array($value, $this->maturitylevels)) {
                return null;
            }
        }

        return strval($value);
    }
}
