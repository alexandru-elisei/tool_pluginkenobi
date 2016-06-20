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
 * File containing tests for the upgrade_generator class.
 *
 * @package    tool_pluginkenobi
 * @copyright  2016 Alexandru Elisei alexandru.elisei@gmail.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/setuplib.php');

/**
 * Upgrade_generator test class.
 *
 * @package    tool_pluginkenobi
 * @copyright  2016 Alexandru Elisei alexandru.elisei@gmail.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_pluginkenobi_upgrade_generator_testcase extends advanced_testcase {

    /** @var string[] Basic recipe. */
    protected static $baserecipe = array(
        'component' => 'upgradegeneratortest',
        'name'      => 'upgrade_generator test',
        'year'      => '2016',
        'author'    => array(
            'name'  => 'Alexandru Elisei',
            'email' => 'alexandru.elisei@gmail.com'
        ),
        'features'  => array(
            'upgrade' => true
        )
    );

    /** @var string Fixture locations. */
    protected static $fixtures;

    /**
     * Sets the fixture location.
     */
    public static function setUpBeforeClass() {
        global $CFG;

        self::$fixtures = $CFG->dirroot . '/admin/tool/pluginkenobi/tests/fixtures/upgrade_generator';
    }

    /**
     * Tidy up open files that may be left open.
     */
    protected function tearDown() {
        gc_collect_cycles();
    }

    /**
     * Tests generating the 'upgrade' feature with no options.
     */
    public function test_no_options() {
        $recipe = self::$baserecipe;
        $targetdir = make_request_directory();

        $generator = new tool_pluginkenobi_upgrade_generator($recipe, $targetdir);
        $generator->generate_files();

        $upgradefile = $targetdir . '/upgradegeneratortest/db/upgrade.php';
        $this->assertFileEquals(self::$fixtures . '/db/upgrade.php', $upgradefile);
    }

    /**
     * Tests generating the 'upgrade' feature with upgradelib.php.
     */
    public function test_with_upgradelib() {
        $recipe = self::$baserecipe;
        $recipe['features']['upgrade'] = array('upgradelib' => true);
        $targetdir = make_request_directory();

        $generator = new tool_pluginkenobi_upgrade_generator($recipe, $targetdir);
        $generator->generate_files();

        $upgradefile = $targetdir . '/upgradegeneratortest/db/upgrade.php';
        $this->assertFileEquals(self::$fixtures . '/db/upgrade_with_upgradelib.php', $upgradefile);

        $upgradelibfile = $targetdir . '/upgradegeneratortest/db/upgradelib.php';
        $this->assertFileEquals(self::$fixtures . '/db/upgradelib.php', $upgradelibfile);
    }
}
