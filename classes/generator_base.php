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
    /** @var string Directory where the plugin files will be generated. */
    protected $targetdir;

    /** @var string[] Recipe for generating the plugin. */
    protected $recipe = array();

    /** @var string[] The files that will be generated by the plugin. */
    protected $generatedfiles = array();

    /** @var $string[] List of features for the plugin. */
    protected $features = array();

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

        // Adding the boilerplate variables.
        foreach (tool_pluginkenobi_processor::$boilerplateoptions as $option) {
            $this->recipe[$option] = $recipe[$option];
        }

        $this->set_target_directory($targetdir, $this->recipe['component']);

        // Core features will always be generated.
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
                    if (!empty($recipe['features'][$feature]) && $recipe['feature'][$feature] == $true) {
                        $requestedfeatures[] = $feature;
                    }
                }
            }
        }

        foreach ($requestedfeatures as $feature) {

            list($required, $invalid) = $this->validate_options($recipe, $this->features[$feature]['requiredoptions'], true);
            if (!is_null($invalid)) {
                throw new moodle_exception('Invalid or missing option "' . $invalid . '"');
            }
            if (!empty($required)) {
                foreach ($required as $option => $value) {
                    $this->recipe[$option] = $value;
                }
            }

            list($optional, $invalid) = $this->validate_options($recipe, $this->features[$feature]['optionaloptions']);
            if (!is_null($invalid)) {
                throw new moodle_exception('Invalid value "' . $recipe[$invalid] . '" for feature ' . $feature);
            }
            if (!empty($optional)) {
                foreach ($optional as $option => $value) {
                    $this->recipe[$option] = $value;
                }
            }

            $this->add_feature_files($feature);
        }
    }

    public function generate_files() {
        global $CFG;

        $result = mkdir($this->targetdir, 0755, true);
        if ($result === false) {
            throw new moodle_exception('Cannot create directory "' . $this->targetdir . '"');
        }

        foreach ($this->generatedfiles as $template => $outputfile) {
            $templatepath = $CFG->dirroot . '/admin/tool/pluginkenobi/' . $template;
            $contents = tool_pluginkenobi_template_processor::load($templatepath, $this->recipe);

            $outputfilepath = $this->targetdir . '/' . $outputfile;
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

            $filehandle = fopen($outputfilepath, 'w');
            fputs($filehandle, $contents);
            fclose($filehandle);
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
    protected function validate_value($option, $value) {
        if (empty($value)) {
            return null;
        } else {
            return $value;
        }
    }

    /**
     * Sets the target directory for the plugin.
     *
     * @param string $targetdir The directory specified by the user.
     * @param string $component The component name.
     */
    protected function set_target_directory($targetdir, $component) {
        list($unused, $plugin) = core_component::normalize_component($component);
        if (empty($targetdir)) {
            $this->targetdir = $CFG->dirroot . '/' . $this->defaultplugindirectory . '/' . $plugin;
        } else {
            $this->targetdir = $targetdir . '/' . $plugin;
        }
    }

    /**
     * Validates the recipe by verifying if the specified options are present.
     *
     * @param string[] $recipe The recipe.
     * @param string[] $options The options to validate against.
     * @param bool $mustexist If the options must exist in the recipe in order to pass validation.
     * @return array as (string[])$recipe => (string | null)$invalid
     */
    protected function validate_options($recipe, $options, $mustexist = false) {
        if (empty($options)) {
            return array(null, null);
        }

        $validated = array();
        foreach ($options as $option) {
            if ($mustexist && empty($recipe[$option])) {
                return array(null, $option);
            }

            if (!empty($recipe[$option])) {
                $value = $this->validate_value($option, $recipe[$option]);
                if (is_null($value)) {
                    return array(null, $option);
                }
                $validated[$option] = $value;
            }
        }

        return array($validated, null);
    }


    protected function add_feature_files($feature) {
        foreach ($this->features[$feature]['files'] as $template => $outputfile) {
            $this->generatedfiles[$template] = $outputfile;
        }
    }
}
