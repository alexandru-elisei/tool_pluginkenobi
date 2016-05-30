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

require_once(__DIR__ . '/generator_base.php');
require_once(__DIR__ . '/template_processor.php');
require_once(__DIR__ . '/processor.php');

/**
 * Lang_generator class.
 *
 * @package    tool_pluginkenobi
 * @copyright  2016 Alexandru Elisei
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_pluginkenobi_lang_generator extends tool_pluginkenobi_generator_base {
    /** @var string[] Options for the generation of the plugin. */
    protected $options = array();

    /** @var string[] Required options for the generation of the plugin. */
    protected $requiredoptions = array('short_description');

    /** @var string The location of the generated plugin.
    protected $targetdir;

    /** @var string[] The templates used for generating the plugin. */
    protected $pluginfiles = array(
        'skel/lang' => ''
    );

    /**
     * Class constructor.
     *
     * @throws moodle_exception.
     * @param string[] $options Generator options.
     * @param string $targetdirectory The directory where the file will be saved.
     */
    public function __construct($options, $targetdir) {
        // TODO: Add support for more locale.
        $this->targetdir = $targetdir . 'lang/en/';

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
        $this->pluginfiles['skel/lang'] = $options['component'] . '.php';
    }
}
