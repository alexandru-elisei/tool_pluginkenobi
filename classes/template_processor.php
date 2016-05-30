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
 * File containing the template_processor class
 *
 * @package    tool_pluginkenobi
 * @copyright  2016 Alexandru Elisei
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/mustache/src/Mustache/Autoloader.php');

/**
 * Template_processor class.
 *
 * @package    tool_pluginkenobi
 * @copyright  2016 Alexandru Elisei
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_pluginkenobi_template_processor {
    /**
     * Process and return the generated template.
     *
     * The filename is relative to the pluginkenobi directory and doesn't
     * include the '.mustache' extension.
     *
     * @throws moodle_exception.
     * @param string $template The template file name.
     */
    public static function generate($templatepath, $options) {
        $templatefile = basename($templatepath) . '.mustache';
        $templatedir = dirname($templatepath);

        Mustache_Autoloader::register();
        $mustache = new Mustache_Engine(array(
            'loader' => new Mustache_Loader_FilesystemLoader($templatedir)
        ));

        $tpl = $mustache->loadTemplate($templatefile);

        return $tpl->render($options);
    }
}
