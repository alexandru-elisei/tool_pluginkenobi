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

require_once($CFG->libdir . '/classes/component.php');
require_once(__DIR__ . '/generator_base.php');
require_once(__DIR__ . '/template_processor.php');
require_once(__DIR__ . '/processor.php');

/**
 * Example_generator class.
 *
 * @package    tool_pluginkenobi
 * @copyright  2016 Alexandru Elisei
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_pluginkenobi_example_generator extends tool_pluginkenobi_generator_base {
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

    /** @var string[] The templates used for generating the plugin. */
    protected $pluginfiles = array(
        'skel/example/example' => 'example.php'
    );

    /** @var $string[] List of files for the optional features. */
    protected $featurefiles = array(
        'settings' => array('skel/example/settings' => 'settings.php'),
    );

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

        $this->options['author']['name'] = $options['author']['name'];
        $this->options['author']['email'] = $options['author']['email'];

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

        $requestedfeatures = array();
        if (isset($options['features']) && is_array($options['features'])) {
            foreach ($options['features'] as $entry) {
                $value = reset($entry);
                $option = key($entry);
                $requestedfeatures[$option] = $value;
            }
        }

        // Adding the files for the features requested by the user.
        if (!empty($requestedfeatures)) {
            if (!empty($requestedfeatures['all'])) {
                if ($requestedfeatures['all'] === true) {
                    foreach ($this->featurefiles as $pluginfiles) {
                        foreach ($pluginfiles as $template => $outputfile) {
                            $this->pluginfiles[$template] = $outputfile;
                        }
                    }
                }
            } else {
                foreach ($this->featurefiles as $option => $pluginfiles) {
                    if (!empty($requestedfeatures[$option]) && $requestedfeatures[$option] === true) {
                        foreach ($pluginfiles as $template => $outputfile) {
                            $this->pluginfiles[$template] = $outputfile;
                        }
                    }
                }
            }
        }

        list($unused, $plugin) = core_component::normalize_component($this->options['component']);
        if (empty($targetdir)) {
            $this->targetdir = $CFG->dirroot . $this->defaultplugindirectory . $plugin . '/';
        } else {
            $this->targetdir = $targetdir . $plugin . '/';
        }
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

        if (empty($value)) {
            return null;
        } else {
            return $value;
        }
    }
}
