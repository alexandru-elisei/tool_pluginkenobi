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
 * File containing the lang_generator class.
 *
 * @package    tool_pluginkenobi
 * @copyright  2016 Alexandru Elisei
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/template_processor.php');
require_once(__DIR__ . '/processor.php');

/**
 * Lang_generator class.
 *
 * @package    tool_pluginkenobi
 * @copyright  2016 Alexandru Elisei
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_pluginkenobi_lang_generator {
    /** @var string[] Options for the generation of the plugin. */
    protected $options = array();

    /** @var string[] Required options for the generation of the plugin. */
    protected $requiredoptions = array('short_description');

    /** @var string The location of the generated plugin relative to the Moodle directory. */
    protected $targetdirectory;

    /** @var string The directory where the templates reside. */
    protected $templatedirectory = 'skel/';

    /** @var string[] The templates used for generating the plugin. */
    protected $templatefiles = array('lang');

    /**
     * Class constructor.
     *
     * @throws moodle_exception.
     * @param string[] $options Generator options.
     * @param string $targetdirectory The directory where the file will be saved.
     */
    public function __construct($options, $targetdirectory = null) {
        $this->targetdirectory = $targetdirectory;
        // Adding the boilerplate variabiles.
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
    }

    public function generate() {
        global $CFG;

        $templatedirectory = $CFG->dirroot . '/admin/tool/pluginkenobi/' . $this->templatedirectory;
        foreach ($this->templatefiles as $file) {
            print("Template for $file: \n\n");
            $result = tool_pluginkenobi_template_processor::generate($file, $templatedirectory, $this->options);
            print($result);
            print("\n");
        }
    }

    /**
     * Checks if a given option is valid.
     *
     * @param string $option The option to be validated.
     * @return string | null The validated option value or null if it's not a valid value.
     */
    protected function validate_option($option, $value) {
        return strval($value);
    }
}
