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
 * File containing tests for the uninstall_generator class.
 *
 * @package    tool_pluginkenobi
 * @copyright  2016 Alexandru Elisei alexandru.elisei@gmail.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/setuplib.php');

/**
 * Uninstall_generator test class.
 *
 * @package    tool_pluginkenobi
 * @copyright  2016 Alexandru Elisei alexandru.elisei@gmail.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_pluginkenobi_uninstall_generator_testcase extends advanced_testcase {

    /** @var string[] Basic recipe. */
    protected static $baserecipe = array(
        'component' => 'uninstallgeneratortest',
        'name'      => 'uninstall_generator test',
        'year'      => '2016',
        'author'    => array(
            'name'  => 'Alexandru Elisei',
            'email' => 'alexandru.elisei@gmail.com'
        ),
    );

    /** @var string Fixture locations. */
    protected static $fixtures;

    /**
     * Sets the fixture location.
     */
    public static function setUpBeforeClass() {
        global $CFG;

        self::$fixtures = $CFG->dirroot . '/admin/tool/pluginkenobi/tests/fixtures/uninstall_generator';
    }

    /**
     * Tidy up open files that may be left open.
     */
    protected function tearDown() {
        gc_collect_cycles();
    }

    /**
     * Tests generating the file.
     */
    public function test_generate_files() {
        $recipe = self::$baserecipe;
        $targetdir = make_request_directory();

        $generator = new tool_pluginkenobi_uninstall_generator($recipe, $targetdir);
        $generator->generate_files();

        $uninstallfile = $targetdir . '/uninstallgeneratortest/db/uninstall.php';
        $this->assertFileEquals($uninstallfile, self::$fixtures . '/db/uninstall.php');
    }
}
