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
 * File containing the events_generator class.
 *
 * @package    tool_pluginkenobi
 * @copyright  2016 Alexandru Elisei
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/generator_base.php');
require_once(__DIR__ . '/processor.php');

/**
 * Events_generator class.
 *
 * The class will generate the classes located at classes/event/.
 *
 * @package    tool_pluginkenobi
 * @copyright  2016 Alexandru Elisei
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_pluginkenobi_events_generator extends tool_pluginkenobi_generator_base {
    /** @var string[] The plugin features. */
    protected $features = array(
        'core'  => array(
            'requiredoptions'   => array('eventname'),
            'optionaloptions'   => array('extends'),
            'files'             => array()
        ));

    /** @var string The default base class for an event. */
    protected $defaultbaseclass = '\core\event\base';

    /**
     * Extracts and validates the options needed for the feature.
     *
     * @throws moodle_exception
     * @param string $feature The feature name.
     * @param string[] $recipe The plugin recipe.
     */
    protected function process_feature_options($feature, $recipe) {
        if ($feature === 'core') {
            if (empty($recipe['features']['events']) || !is_array($recipe['features']['events'])) {
                throw new moodle_exception('Missing or invalid feature "events"');
            }

            foreach ($recipe['features']['events'] as $key => $event) {
                $event = $this->validate_event($event);
                $this->recipe['features']['events'][$key] = $event;
            }
        } else {
            parent::process_feature_options($feature, $recipe);
        }
    }

    /**
     * Validates the options for an event.
     *
     * @throws moodle_exception
     * @param string[] $capability The event to be validated.
     */
    protected function validate_event($event) {
        $ret = array();
        foreach ($this->features['core']['requiredoptions'] as $option) {
            if (empty($event[$option])) {
                throw new moodle_exception('Missing required option "' . $option . '"');
            }
            $ret[$option] = $this->validate_value($option, $event[$option]);
        }

        foreach($this->features['core']['optionaloptions'] as $option) {
           if (!empty($event[$option])) {
                $value = $this->validate_value($option, $event[$option]);
                $ret[$option] = $value;
            }
        }

        return $ret;
    }

    /**
     * Checks if a given option is valid.
     *
     * @param string $option The option to be validated.
     * @return string The validated value.
     */
    protected function validate_value($option, $value) {
        if ($option === 'extends') {
            $isnamespace = strpos($value, '\\');
            if ($isnamespace === false) {
                throw new moodle_exception('Missing or invalid namespace for "extends" option');
            }
            return $value;
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
        foreach ($this->recipe['features']['events'] as $event) {
            $filerecipe = $this->recipe;
            unset($filerecipe['features']);

            $eventname = $event['eventname'];
            $filerecipe['eventname'] = $eventname;
            $extends = empty($event['extends']) ? $this->defaultbaseclass : $event['extends'];
            $filerecipe['extends'] = $extends;

            $outputfile = 'classes/event/' . $eventname . '.php';
            $this->outputfiles[$outputfile] = array(
                'template'  => 'skel/classes/event/event',
                'recipe'    => $filerecipe
            );
        }
    }
}
