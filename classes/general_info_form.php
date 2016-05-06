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
 * File containing the general information form.
 *
 * @package    tool_pluginkenobi
 * @copyright  2016 Alexandru Elisei
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * General information form.
 *
 * @package    tool_pluginkenobi
 * @copyright  2016 Alexandru Elisei
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_pluginkenobi_general_info_form extends moodleform {

    /**
     * The standard form definiton.
     */
    public function definition () {
        $mform = $this->_form;

        $mform->addElement('header', 'recipehdr', get_string('recipehdr', 'tool_pluginkenobi'));
        $mform->setExpanded('recipehdr', true);
        $mform->addElement('filepicker', 'recipefile', get_string('recipefile', 'tool_pluginkenobi'),
                           null, array('maxbytes' => 50000, 'accepted_types' => '*'));
        $mform->addElement('submit', 'loadrecipe', get_string('loadrecipe', 'tool_pluginkenobi'));

        $mform->addElement('header', 'manualhdr', get_string('manualhdr', 'tool_pluginkenobi'));
        $mform->setExpanded('manualhdr', true);
        $mform->closeHeaderBefore('manualhdr');

        $plugintypes = array(
            'mod'   => get_string('mod', 'tool_pluginkenobi'),
            'tool'  => get_string('tool', 'tool_pluginkenobi')
        );
        $mform->addElement('select', 'setplugintype',
                           get_string('setplugintype', 'tool_pluginkenobi'), $plugintypes);
        $mform->setDefault('setplugintype', $plugintypes['mod']);

        $mform->addElement('text', 'setname', get_string('setname', 'tool_pluginkenobi'),
                           'size="17"');
        $mform->setType('setname', PARAM_TEXT);
        $mform->addElement('text', 'setversion', get_string('setversion', 'tool_pluginkenobi'),
                           'size="17"');
        $mform->setType('setversion', PARAM_INT);
        $mform->addElement('text', 'setrequires', get_string('setrequires', 'tool_pluginkenobi'),
                           'size="17"');
        $mform->setType('setrequires', PARAM_INT);

        $pluginmaturities = array(
            'alpha' => 'MATURITY_ALPHA',
            'beta'  => 'MATURITY_BETA',
            'rc'    => 'MATURITY_RC',
            'stable'=> 'MATURITY_STABLE'
        );
        $mform->addElement('select', 'setmaturity',
                           get_string('setmaturity', 'tool_pluginkenobi'), $pluginmaturities);
        $mform->setDefault('setmaturity', $pluginmaturities['alpha']);

        $mform->addElement('text', 'setrelease', get_string('setrelease', 'tool_pluginkenobi'),
                           'size="17"');
        $mform->setType('setrelease', PARAM_TEXT);
        $mform->addElement('advcheckbox', 'setwebinterface',
                           get_string('setwebinterface', 'tool_pluginkenobi'), '', null, array(0, 1));
        $mform->addElement('advcheckbox', 'setcliscripts',
                           get_string('setcliscripts', 'tool_pluginkenobi'), '', null, array(0, 1));

        $buttonarr = array();
        $buttonarr[] =& $mform->createElement('submit', 'next', get_string('next', 'tool_pluginkenobi'));
        $buttonarr[] =& $mform->createElement('submit', 'skiptogeneration', get_string('skiptogeneration', 'tool_pluginkenobi'));
        $mform->disabledIf('skiptogeneration', 'setwebinterface', 'eq', '0');
        $mform->addGroup($buttonarr, 'buttonarr', '', array(' '), false);
        $mform->closeHeaderBefore('buttonarr');
    }
}
