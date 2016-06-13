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

        // Extracting author name and email.
        if (!empty($recipe['author']) && is_array($recipe['author'])) {
            foreach ($recipe['author'] as $entry) {
                if (!empty($entry['name'])) {
                    $recipe['author']['name'] = $entry['name'];
                } else if (!empty($entry['email'])) {
                    $recipe['author']['email'] = $entry['email'];
                }
            }
        }

        // Preparing the requested features.
        if (!empty($recipe['features']) && is_array($recipe['features'])) {
            $requestedfeatures = self::prepare_features($recipe['features']);
            $recipe['features'] = $requestedfeatures;
        }

        // Preparing the capabilities.
        if (!empty($recipe['capabilities']) && is_array($recipe['capabilities'])) {
            $capabilities = self::prepare_capabilities($recipe['capabilities']);
            $recipe['capabilities'] = $capabilities;
        }

        return $recipe;
    }

    /**
     * Prepares the recipe features in a format understood by the generators.
     *
     * @param string[] $features The list of a features.
     * @return string[] The modified list of features.
     */
    protected static function prepare_features($features) {
        $preparedfeatures = array();
        foreach ($features as $feature) {
            $option = key($feature);
            $value = reset($feature);
            $preparedfeatures[$option] = $value;
        }

        return $preparedfeatures;
    }

    /**
     * Returns the list of capabilities in a format understood by the db_access_generator.
     *
     * @param string[] $capabilities The list of capabilities from the recipe.
     * @return string[] The capabilities for the generator.
     */
    protected static function prepare_capabilities($capabilities) {
        $ret = array();
        foreach ($capabilities as $index => $description) {
            foreach ($description as $name => $cap) {
                $ret[$index]['capname'] = $name;
                foreach ($cap as $notused => $fieldarr) {
                    $fieldname = key($fieldarr);
                    $fieldvalue = reset($fieldarr);
                    if ($fieldname === 'archetypes') {
                        $ret[$index]['archetypes'] = array();
                        foreach ($fieldvalue as $key => $archetypedescription) {
                            $role = key($archetypedescription);
                            $permission = reset($archetypedescription);
                            $ret[$index]['archetypes'][$key] = array(
                                'role' => $role,
                                'permission' => $permission
                            );
                        }
                    } else {
                        $ret[$index][$fieldname] = $fieldvalue;
                    }
                }
            }
        }

        return $ret;
    }
}
