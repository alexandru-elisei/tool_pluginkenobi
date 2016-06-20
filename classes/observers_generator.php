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
 * File containing the observers_generator class.
 *
 * @package    tool_pluginkenobi
 * @copyright  2016 Alexandru Elisei
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/generator_base.php');
require_once(__DIR__ . '/processor.php');

/**
 * Observers_generator class.
 *
 * The class will generate the db/events.php file and the observer classes.
 *
 * @package    tool_pluginkenobi
 * @copyright  2016 Alexandru Elisei
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_pluginkenobi_observers_generator extends tool_pluginkenobi_helper_generator_base {
    /** @var string[] The plugin features. */
    protected $features = array(
        'core'  => array(
            'requiredoptions'   => array('eventname', 'callback'),
            'optionaloptions'   => array('includefile', 'priority', 'internal'),
            'files'             => array(
                'db/events.php' => array('template' => 'skel/db/events')
            )),
    );

    /** @var string What feature the helper generator is implementing. */
    protected $implementedfeature = 'observers';

    /**
     * Extracts and validates the options needed for the core feature.
     *
     * @throws moodle_exception
     * @param string $feature The feature name.
     * @param string[] $recipe The plugin recipe.
     */
    protected function process_feature_options($feature, $recipe) {
        if (empty($recipe['features']['observers']) || !is_array($recipe['features']['observers'])) {
            throw new moodle_exception('Missing or invalid feature "observers"');
        }

        $options = $this->get_feature_options($feature, $recipe);
        foreach ($options as $key => $observer) {
            $expected = $this->features['core']['requiredoptions'];
            $required = $this->validate_options($observer, $expected, true);
            foreach ($required as $option => $value) {
                $this->recipe['features']['observers'][$key][$option] = $value;
            }

            $expected = $this->features['core']['optionaloptions'];
            $optional = $this->validate_options($observer, $expected);
            foreach ($optional as $option => $value) {
                $this->recipe['features']['observers'][$key][$option] = $value;
            }
        }
    }

    /**
     * Checks if a given option is valid.
     *
     * @param string $option The option to be validated.
     * @return string The validated value.
     */
    protected function validate_value($option, $value) {
        if ($option === 'eventname' || $option == 'callback') {
            $hasnamespace = strpos($value, '\\');
            if ($hasnamespace === false) {
                throw new moodle_exception('Missing or invalid namespace for ' . $option);
            }
            if ($option == 'callback') {
                $namespace = explode('\\', $value);
                $namefunction = end($namespace);

                if (strpos($namefunction, '::') === false) {
                    throw new moodle_exception('Invalid callback class of function name');
                }

                list($name, $function) = explode('::', $namefunction, 2);
                if (empty($name) or empty($function)) {
                    throw new moodle_exception('Missing or invalid observer or function for the callback');
                }
            }
            return $value;
        } else if ($option === 'priority') {
            if (!is_numeric($value)) {
                throw new moodle_exception('Invalid value for priority');
            }
            $value = (int) $value;
            if ($value < 0) {
                throw new moodle_exception('Negative value for priority');
            }
            return $value;
        } else if ($option === 'internal') {
            if (!is_bool($value)) {
                throw new moodle_excpetion('Missing or invalid boolean value "' . $value . '"');
            }
            return (bool) $value;
        } else {
            return parent::validate_value($option, $value);
        }
    }

    /**
     * Adds the observer class files to be created and their recipe options.
     *
     * @param string[] $recipe The recipe.
     */
    protected function execute_additional_steps($recipe) {
        foreach ($this->recipe['features']['observers'] as $observer) {
            $filerecipe = $this->recipe;
            $filerecipe['features'] = array();

            $filerecipe['features']['observers']['eventname'] = trim($observer['eventname'], '"\'');
            $eventnamespace = explode('\\', $observer['eventname']);
            if (!is_array($eventnamespace)) {
                throw new moodle_exception('Invalid value for eventname');
            }
            $filerecipe['features']['observers']['eventclassname'] = end($eventnamespace);

            $callback = trim($observer['callback'], '"\'');
            $observernamespace = explode('\\', $callback);
            $namefunction = end($observernamespace);
            list($name, $function) = explode('::', $namefunction, 2);
            $filerecipe['features']['observers']['name'] = $name;
            $filerecipe['features']['observers']['function'] = $function;

            $outputfile = 'classes/' . $name . '.php';
            $this->outputfiles[$outputfile] = array(
                'template'  => 'skel/classes/event_observer',
                'recipe'    => $filerecipe
            );
        }
    }
}
