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
 * File containing the capabilities_generator class.
 *
 * @package    tool_pluginkenobi
 * @copyright  2016 Alexandru Elisei
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/generator_base.php');
require_once(__DIR__ . '/processor.php');

/**
 * Capabilities_generator class.
 *
 * The class will generate the db/access.php file.
 *
 * @package    tool_pluginkenobi
 * @copyright  2016 Alexandru Elisei
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_pluginkenobi_capabilities_generator extends tool_pluginkenobi_generator_base {
    /** @var string[] The plugin features. */
    protected $features = array(
        'core'  => array(
            'requiredoptions'   => array('name', 'captype', 'contextlevel', 'archetypes'),
            'optionaloptions'   => array('riskbitmask', 'clonepermissionsfrom'),
            'files'             => array(
                'db/access.php' => array('template' => 'skel/db/access')
            )),
    );

    /** @var string[] The risks associated with the capability. */
    protected $riskbitmasks = array('RISK_SPAM', 'RISK_PERSONAL', 'RISK_XSS', 'RISK_CONFIG');

    /** @var string[] The type of capabilities. */
    protected $captypes = array('write', 'read');

    /** @var string[] Contexts for the capability. */
    protected $contextlevels = array('CONTEXT_MODULE', 'CONTEXT_COURSE', 'CONTEXT_BLOCK', 'CONTEXT_SYSTEM',
                                     'CONTEXT_USER', 'CONTEXT_COURSECAT');

    /** @var string[] List of possible permission for an archetype. */
    protected $permissions = array('CAP_ALLOW', 'CAP_PREVENT');

    /**
     * Extracts and validates the options needed for the feature.
     *
     * @throws moodle_exception
     * @param string $feature The feature name.
     * @param string[] $recipe The plugin recipe.
     */
    protected function process_feature_options($feature, $recipe) {
        if ($feature === 'core') {
            if (empty($recipe['features']['capabilities']) || !is_array($recipe['features']['capabilities'])) {
                throw new moodle_exception('Missing or invalid feature "capabilities"');
            }

            foreach ($recipe['features']['capabilities'] as $key => $capability) {
                $capability = $this->validate_capability($capability);
                $this->recipe['features']['capabilities'][$key] = $capability;
            }
        } else {
            parent::process_feature_options($feature, $recipe);
        }
    }

    /**
     * Validates the options for a capability.
     *
     * @throws moodle_exception
     * @param string[] $capability The capability to be validated.
     */
    protected function validate_capability($capability) {
        $ret = array();
        foreach ($this->features['core']['requiredoptions'] as $option) {
            if (empty($capability[$option])) {
                throw new moodle_exception('Missing required option "' . $option . '"');
            }

            // Validating each of the archetypes.
            if ($option === 'archetypes' && !empty($capability['archetypes'])) {
                if (!is_array($capability['archetypes'])) {
                    throw new moodle_exception('Invalid format for capability archetypes');
                }
                $ret['archetypes'] = array();
                foreach ($capability['archetypes'] as $key => $archetype) {
                    $role = $this->validate_value('role', $archetype['role']);
                    $permission = $this->validate_value('permission', $archetype['permission']);
                    $ret['archetypes'][$key] = array(
                        'role' => $role,
                        'permission' => $permission
                    );
                }
            } else {
                $ret[$option] = $this->validate_value($option, $capability[$option]);
            }
        }

        foreach($this->features['core']['optionaloptions'] as $option) {
           if (!empty($capability[$option])) {
                $value = $this->validate_value($option, $capability[$option]);
                $ret[$option] = $value;
            }
        }

        return $ret;
    }

    /**
     * Checks if a given option is valid.
     *
     * @param string $option The option to be validated.
     * @return string | null The validated option value or null if it's not a valid value.
     */
    protected function validate_value($option, $value) {
        if ($option === 'riskbitmask') {
            $riskbitmasks = explode('|', $value);
            $ret = array();
            foreach ($riskbitmasks as $riskbitmask) {
                $bitmask = trim($riskbitmask);
                if (!in_array($bitmask, $this->riskbitmasks)) {
                    throw new moodle_exception('Unknown riskbitmask "' . $bitmask . '"');
                }
                $ret[] = $bitmask;
            }
            $ret = implode(' | ', $ret);
            return $ret;
        } else if ($option === 'captype') {
            if (!in_array($value, $this->captypes)) {
                throw new moodle_exception('Unknwon captype "' . $value . '"');
            }
            return $value;
        } else if ($option === 'contextlevel') {
            if (!in_array($value, $this->contextlevels)) {
                throw new moodle_excpetion('Unknown contextlevel "' . $value . '"');
            }
            return $value;
        } else if ($option === 'permission') {
            if (!in_array($value, $this->permissions)) {
                throw new moodle_exception('Unknown permission "' . $value . '"');
            }
            return $value;
        }

        return parent::validate_value($option, $value);
    }

    /**
     * Adds the plugin type and name to be used by the template.
     *
     * @param string[] $recipe The recipe.
     */
    protected function execute_additional_steps($recipe) {
        list($type, $plugin) = core_component::normalize_component($this->component);
        $this->recipe['type'] = $type;
        $this->recipe['plugin'] = $plugin;
    }
}
