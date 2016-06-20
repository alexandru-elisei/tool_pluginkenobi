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
 * File containing tests for the version_generator class.
 *
 * @package    tool_pluginkenobi
 * @copyright  2016 Alexandru Elisei alexandru.elisei@gmail.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/setuplib.php');

/**
 * version_generator test class.
 *
 * @package    tool_pluginkenobi
 * @copyright  2016 Alexandru Elisei alexandru.elisei@gmail.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_pluginkenobi_version_generator_testcase extends advanced_testcase {

    /** @var string[] Basic recipe. */
    protected static $baserecipe = array(
        'component' => 'test_versiongeneratortest',
        'name'      => 'version_generator test',
        'release'   => '0.1',
        'year'      => 2016,        // For the boilerplate.
        'author'    => array(
            'name'  => 'Alexandru Elisei',
            'email' => 'alexandru.elisei@gmail.com'
        ),
        'version'   => '2016121200',
        'requires'  => '2.9',
    );

    /** @var string Fixture locations. */
    protected static $fixtures;

    /**
     * Sets the fixture location.
     */
    public static function setUpBeforeClass() {
        global $CFG;

        self::$fixtures = $CFG->dirroot . '/admin/tool/pluginkenobi/tests/fixtures/version_generator';
    }

    /**
     * Tidy up open files that may be left open.
     */
    protected function tearDown() {
        gc_collect_cycles();
    }

    /**
     * Tests generating a version.php file with missing required options.
     */
    public function test_missing_options() {
        $recipe = self::$baserecipe;
        unset($recipe['version']);

        $this->setExpectedException('moodle_exception');
        $generator = new tool_pluginkenobi_version_generator($recipe, '');
    }

    /**
     * Tests generating a version.php file with options.
     */
    public function test_options() {
        $recipe = self::$baserecipe;
        $recipe['features'] = array(
            'all' => false
        );
        $recipe['maturity'] = 'MATURITY_ALPHA';
        $targetdir = make_request_directory() . '/';

        $generator = new tool_pluginkenobi_version_generator($recipe, $targetdir);
        $generator->generate_files();

        $versionfile = $targetdir . '/versiongeneratortest/version.php';
        $this->assertFileEquals(self::$fixtures . '/version_generator_test_options.php', $versionfile);
    }
}
