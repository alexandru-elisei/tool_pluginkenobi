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
 * File containing the recipe_processor class
 *
 * @package    tool_pluginkenobi
 * @copyright  2016 Alexandru Elisei
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../Spyc/Spyc.php');
require_once(__DIR__ . '/../../../../config.php');

/**
 * Recipe_processor class.
 *
 * @package    tool_pluginkenobi
 * @copyright  2016 Alexandru Elisei
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_pluginkenobi_recipe_processor {
    /**
     * Process and return the recipe file.
     *
     * The filename is absolute and must include the '.yaml' extension.
     *
     * @throws moodle_exception.
     * @param string $filename The yaml file.
     */
    public static function load($filename) {
        try {
            $recipe = Spyc::YAMLLoad($filename);
        } catch (Exception $e) {
            throw new moodle_exception('Error loading file "' . $filename . '": ' . $e->getMessage());
        }

        return $recipe;
    }
}
