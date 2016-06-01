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

/**
 * Generator_base class.
 *
 * @package    tool_pluginkenobi
 * @copyright  2016 Alexandru Elisei
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class tool_pluginkenobi_generator_base {
    /** @var string[] The files for the plugin in the form template => file. */
    protected $pluginfiles = array();

    /** @var string Directory where the plugin files will be generated. */
    protected $targetdir;

    /** @var string[] Options for the generation of the plugin. */
    protected $options = array();

    /**
     * Class constructor.
     *
     * @throws moodle_exception.
     * @param string[] $options Generator options.
     * @param string $targetdir Target directory.
     */
    abstract public function __construct($options, $targetdir);

    public function generate() {
        global $CFG;

        $result = mkdir($this->targetdir, 0755, true);
        if ($result === false) {
            throw new moodle_exception('Cannot create directory "' . $this->targetdir . '"');
        }

        foreach ($this->pluginfiles as $template => $outputfile) {
            $templatepath = $CFG->dirroot . '/admin/tool/pluginkenobi/' . $template;
            $contents = tool_pluginkenobi_template_processor::generate($templatepath, $this->options);

            $outputfilepath = $this->targetdir . $outputfile;
            if (file_exists($outputfilepath)) {
                throw new moodle_exception('File "' . $outputfilepath . '" already exists');
            } else {
                $dirpath = basename($outputfilepath);
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
    protected function validate_option($option, $value) {
        if (empty($value)) {
            return null;
        } else {
            return $value;
        }
    }
}
