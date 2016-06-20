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
 * File containing the generator_base class.
 *
 * @package    tool_pluginkenobi
 * @copyright  2016 Alexandru Elisei
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/template_processor.php');
require_once(__DIR__ . '/processor.php');

/**
 * Generator_base class.
 *
 * @package    tool_pluginkenobi
 * @copyright  2016 Alexandru Elisei
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class tool_pluginkenobi_generator_base {
    /** @var string The component name. */
    protected $component;

    /** @var string Default plugin location. */
    protected $defaultlocation;

    /** @var string Directory where the plugin files will be generated. */
    protected $targetdir;

    /** @var string[] Recipe for generating the plugin. */
    protected $recipe = array();

    /** @var string[] The files that will be generated by the plugin. */
    protected $outputfiles = array();

    /** @var $string[] List of features for the plugin. */
    protected $features = array();

    /** @var $stdClass[] List of helper generators to be used when generating the files. */
    protected $helpergenerators = array();

    /**
     * Class constructor.
     *
     * @throws moodle_exception.
     * @param string[] $recipe Plugin recipe.
     * @param string $targetdir The target directory.
     */
    public function __construct($recipe, $targetdir) {
        global $CFG;

        $this->recipe['author']['name'] = $recipe['author']['name'];
        $this->recipe['author']['email'] = $recipe['author']['email'];
        $this->component = $recipe['component'];
        $this->set_target_directory($targetdir);

        // Adding the boilerplate variables.
        foreach (tool_pluginkenobi_processor::$boilerplateoptions as $option) {
            $this->recipe[$option] = $recipe[$option];
        }

        $requestedfeatures = $this->get_requested_features($recipe);
        foreach ($requestedfeatures as $feature) {
            if (is_array($this->features[$feature])) {
                $this->process_feature_options($feature, $recipe);
                $this->add_feature_files($feature);
            } else {
                require_once(__DIR__ . '/' . $this->features[$feature] . '.php');
                $generatorname = 'tool_pluginkenobi_' . $this->features[$feature];
                $helper = new $generatorname($recipe, $this->targetdir);
                $this->helpergenerators[] = $helper;
            }
        }

        $this->execute_additional_steps($recipe);
    }

    /**
     * Generates all the files needed for the plugin.
     */
    public function generate_files() {
        global $CFG;

        $pluginpath = $this->prepare_plugin_path();
        foreach ($this->outputfiles as $outputfile => $fileoptions) {
            // Preparing the location of the template file and the generated file.
            $template = $fileoptions['template'];
            $templatepath = $CFG->dirroot . '/admin/tool/pluginkenobi/' . $template;
            $outputfilepath = $this->prepare_file_path($pluginpath, $outputfile);

            if (!empty($fileoptions['recipe'])) {
                $recipe = $fileoptions['recipe'];
            } else {
                $recipe = $this->recipe;
            }
            $contents = tool_pluginkenobi_template_processor::load($templatepath, $recipe);

            $filehandle = fopen($outputfilepath, 'w');
            fputs($filehandle, $contents);
            fclose($filehandle);
        }

        foreach ($this->helpergenerators as $generator) {
            $generator->generate_files();
        }
    }

    /**
     * Returns the array of features requested in the recipe.
     *
     * @param string[] $recipe The recipe.
     * @return string[] The requested features.
     */
    protected function get_requested_features($recipe) {
        // Core generator components will always be generated.
        $requestedfeatures = array('core');
        if (!empty($recipe['features']) && is_array($recipe['features'])) {
            if (!empty($recipe['features']['all'])) {
                foreach ($this->features as $feature => $notused) {
                    if ($feature !== 'core') {
                        $requestedfeatures[] = $feature;
                    }
                }
            } else {
                foreach ($this->features as $feature => $notused) {
                    if (!empty($recipe['features'][$feature])) {
                        $requestedfeatures[] = $feature;
                    }
                }
            }
        }

        return $requestedfeatures;
    }

    /**
     * Returns the directory where the plugin will be created.
     *
     * This directory does not include the plugin name.
     *
     * @return string The target directory.
     */
    public function get_target_directory() {
        return $this->targetdir;
    }

    /**
     * Sets the value for the target directory based on the user input or default plugin location.
     *
     * @param string $targetdir The user supplied target directory.
     * @return string The target directory.
     */
    protected function set_target_directory($targetdir) {
        global $CFG;

        $defaultlocationpath = $CFG->dirroot . '/' . $this->defaultlocation;
        $this->targetdir = empty($targetdir) ? $defaultlocationpath : $targetdir;
    }

    /**
     * Prepares the path to the directory where the plugin's files will be generated.
     * All the subdirectories on the path will be generated.
     *
     * @param string $targetdir The directory specified by the user.
     * @param string $component The component name.
     */
    protected function prepare_plugin_path() {
        list($unused, $plugin) = core_component::normalize_component($this->component);
        $pluginpath = $this->targetdir . '/' . $plugin;

        if (!file_exists($pluginpath)) {
            $result = mkdir($pluginpath, 0755, true);
            if ($result === false) {
                throw new moodle_exception('Cannot create directory "' . $pluginpath . '"');
            }
        }

        return $pluginpath;
    }

    /**
     * Prepares the location of a file by creating all the necessary subdirectories.
     *
     * @param string $pluginpath The target path for the plugin.
     * @param string $filepath The file path.
     * @return string The prepared path.
     */
    protected function prepare_file_path($pluginpath, $filepath) {
        $outputfilepath = $pluginpath . '/' . $filepath;
        if (file_exists($outputfilepath)) {
            throw new moodle_exception('File "' . $outputfilepath . '" already exists');
        } else {
            $dirpath = dirname($outputfilepath);
            // Creating the directory hierarchy if it doesn't exist.
            if (!file_exists($dirpath)) {
                $result = mkdir($dirpath, 0755, true);
                if ($result === false) {
                    throw new moodle_exception('Cannot create directory "' . $dirpath . '"');
                }
            }
        }

        return $outputfilepath;
    }

    /**
     * Extracts and validates the options needed for the feature.
     *
     * @throws moodle_exception
     * @param string $feature The feature name.
     * @param string[] $recipe The plugin recipe.
     */
    protected function process_feature_options($feature, $recipe) {
        $options = $this->get_feature_options($feature, $recipe);
        $expected = $this->features[$feature]['requiredoptions'];
        $required = $this->validate_options($options, $expected, true);
        if (!empty($required)) {
            foreach ($required as $option => $value) {
                if ($feature === 'core') {
                    $this->recipe[$option] = $value;
                } else {
                    $this->recipe['features'][$feature][$option] = $value;
                }
            }
        }

        $expected = $this->features[$feature]['optionaloptions'];
        $optional = $this->validate_options($options, $expected);
        if (!empty($optional)) {
            foreach ($optional as $option => $value) {
                if ($feature === 'core') {
                    $this->recipe[$option] = $value;
                } else {
                    $this->recipe['features'][$feature][$option] = $value;
                }
            }
        }
    }

    /**
     * Returns a list of the options specified in the recipe for $feature.
     *
     * @param string $feature
     * @param string[] $recipe
     */
    protected function get_feature_options($feature, $recipe) {
        if ($feature === 'core') {
            return $recipe;
        } else {
            return $recipe['features'][$feature];
        }
    }

    /**
     * Validates the recipe by verifying if the specified options are present.
     *
     * @param string[] $options The options from the recipe.
     * @param string[] $expected The options to validate against.
     * @param bool $mustexist If the options must exist in the recipe in order to pass validation.
     * @return string[] The validated options.
     */
    protected function validate_options($options, $expected, $mustexist = false) {
        // If the feature value is a boolean, then return an empty array of options.
        // This happens when the feature has no options associated with it.
        if (!is_array($options)) {
            return array();
        }

        $validated = array();
        foreach ($expected as $option) {
            if ($mustexist && empty($options[$option])) {
                throw new moodle_exception('Required option "' . $option . '" missing');
            }

            if (!empty($options[$option])) {
                $value = $this->validate_value($option, $options[$option]);
                $validated[$option] = $value;
            }
        }

        return $validated;
    }

    /**
     * Checks if a given option is valid.
     *
     * @param string $option The option to be validated.
     * @return string | null The validated option value or null if it's not a valid value.
     */
    protected function validate_value($option, $value) {
        return $value;
    }

    /**
     * Adds all the files needed by $feature to the list of generated files.
     *
     * @param string $feature The feature name.
     */
    protected function add_feature_files($feature) {
        if (!empty($this->features[$feature]['files'])) {
            foreach ($this->features[$feature]['files'] as $outputfile => $fileoptions) {
                $this->outputfiles[$outputfile]['template'] = $fileoptions['template'];
            }
        }
    }

    /**
     * Executes additional steps required for the creation of the recipe.
     *
     * This function will be overridden by derived generator classes.
     *
     * @param string[] $recipe The recipe.
     */
    protected function execute_additional_steps($recipe) {
        return;
    }
}
