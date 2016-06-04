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
 * File containing tests for the example_generator class.
 *
 * @package    tool_pluginkenobi
 * @copyright  2016 Alexandru Elisei alexandru.elisei@gmail.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/setuplib.php');

/**
 * Example_generator test class.
 *
 * @package    tool_pluginkenobi
 * @copyright  2016 Alexandru Elisei alexandru.elisei@gmail.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_pluginkenobi_example_generator_testcase extends advanced_testcase {

    /** @var string[] Basic recipe. */
    protected static $baserecipe = array(
        'component' => 'example_test',
        'name'      => 'Example test',
        'release'  => '0.1',
        'author'    => array(
            'name'  => 'Alexandru Elisei',
            'email' => 'alexandru.elisei@gmail.com'
        ),
        'version'   => '2016121200',
        'requires'  => '2.9',
        'maturity'  => 'MATURITY_ALPHA'
    );

    /** @var string Fixture locations. */
    //protected static $fixtures = $CFG->dirroot . '/admin/tool/pluginkenobi/tests/fixtures/example';
    protected static $fixtures;

    /**
     * Sets the fixture location.
     */
    public static function setUpBeforeClass() {
        global $CFG;

        self::$fixtures = $CFG->dirroot . '/admin/tool/pluginkenobi/tests/fixtures/example';
    }

    /**
     * Tidy up open files that may be left open.
     */
    protected function tearDown() {
        gc_collect_cycles();
    }

    /**
     * Tests generating an 'example' plugin type with missing required options.
     */
    public function test_missing_options() {
        $recipe = self::$baserecipe;
        unset($recipe['author']);

        $this->setExpectedException('moodle_exception');
        $processor = new tool_pluginkenobi_processor($recipe, '');
    }

    /**
     * Tests generating an 'example' plugin type with no features.
     */
    public function test_no_features() {
        $recipe = self::$baserecipe;
        $recipe['features'] = array(
            'all' => false
        );
        $targetdir = make_request_directory() . '/';

        $processor = new tool_pluginkenobi_processor($recipe, $targetdir);
        $processor->generate();

        $examplefile = $targetdir . '/test/example.php';
        $this->assertFileExists($examplefile);
        $this->assertFileEquals($examplefile, self::$fixtures . '/example.php');

        $langfile = $targetdir . '/test/lang/en/example_test.php';
        $this->assertFileExists($langfile);
        $this->assertFileEquals($langfile, self::$fixtures . '/lang/en/example_test.php');
    }

    /**
     * Tests generating an 'example' plugin type with all the features.
     */
    public function test_all_features() {
        $recipe = self::$baserecipe;
        $recipe['features'] = array(
            'all' => true
        );
        $targetdir = make_request_directory() . '/';

        $processor = new tool_pluginkenobi_processor($recipe, $targetdir);
        $processor->generate();

        $examplefile = $targetdir . '/test/example.php';
        $this->assertFileExists($examplefile);
        $this->assertFileEquals($examplefile, self::$fixtures . '/example.php');

        $langfile = $targetdir . '/test/lang/en/example_test.php';
        $this->assertFileExists($langfile);
        $this->assertFileEquals($langfile, self::$fixtures . '/lang/en/example_test.php');

        $settingsfile = $targetdir . '/test/settings.php';
        $this->assertFileExists($settingsfile);
        $this->assertFileEquals($settingsfile, self::$fixtures . '/settings.php');
    }

}
