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
 * File containing the processor class.
 *
 * @package    tool_pluginkenobi
 * @copyright  2016 Alexandru Elisei
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/moodlelib.php');
require_once($CFG->libdir . '/classes/component.php');
require_once(__DIR__ . '/recipe_reader.php');

/**
 * Processor class.
 *
 * @package    tool_pluginkenobi
 * @copyright  2016 Alexandru Elisei
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_pluginkenobi_processor {
    /**
     * Options for the boilerplate.
     */
    static public $boilerplateoptions = array('year', 'component');

    /** @var string $plugintype The type of the plugin. */
    protected $plugintype = null;

    /** @var string[] $recipe The recipe for the generation of the plugin. */
    protected $recipe = array();

    /** @var string[] $supportedplugintypes The supported plugin types. */
    protected $supportedplugintypes = array('example');

    /** @var string[] Templates required for every plugin. */
    protected $requiredtemplates = array('lang');

    /** @var string Target directory for the creation of the plugin files. */
    protected $targetdir = null;

    /**
     * Class constructor.
     *
     * @param string $plugintype The type of plugin.
     * @param string[] $recipe The recipe for the plugin.
     * @param string $recipe Recipe file name.
     */
    public function __construct($recipe, $targetdir = null) {
        $this->recipe = $recipe;
        $this->targetdir = $targetdir;

        if (empty($this->recipe['component'])) {
            throw new moodle_exception('Plugin component not specified in the recipe file');
        }

        $plugin = null;
        list($this->plugintype, $plugin) = core_component::normalize_component($this->recipe['component']);

        if (empty($plugin)) {
            throw new moodle_exception('Invalid plugin component name');
        }

        if (!in_array($this->plugintype, $this->supportedplugintypes)) {
           throw new moodle_exception('Unsupported plugin type "' . $this->plugintype . '"');
        }

        if (empty($this->recipe['author']) || !is_array($this->recipe['author'])) {
            throw new moodle_exception('Author not specified in the recipe file');
        }

        if (empty($this->recipe['author']['name'])) {
            throw new moodle_exception('Author name not specified in the recipe file');
        }

        if (empty($this->recipe['author']['email'])) {
            throw new moodle_exception('Author email not specified in the recipe file');
        }

        // Every template requires the 'year' variable for the boilerplate.
        $this->recipe['year'] = userdate(time(), '%Y');

        foreach (self::$boilerplateoptions as $option) {
            if (empty($this->recipe[$option])) {
                throw new moodle_exception('Option "' . $option . '" required for the boilerplate missing');
            }
        }
    }

    /**
     * Generates a plugin.
     */
    public function generate() {
        global $CFG;

        require_once(__DIR__ . '/' . $this->plugintype . '_generator.php');
        $generatorname = 'tool_pluginkenobi_' . $this->plugintype . '_generator';
        $generator = new $generatorname($this->recipe, $this->targetdir);
        $generator->generate();
        // Update the target directory, the generator might use the default plugin location. */
        $this->targetdir = $generator->get_target_directory();

        // Generating the required files.
        foreach ($this->requiredtemplates as $template) {
            require_once(__DIR__ . '/' . $template . '_generator.php');
            $generatorname = 'tool_pluginkenobi_' . $template . '_generator';
            $generator = new $generatorname($this->recipe, $this->targetdir);
            $generator->generate();
        }
    }
}
