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
 * File containing the tests for the observers_generator class.
 *
 * @package    tool_pluginkenobi
 * @copyright  2016 Alexandru Elisei alexandru.elisei@gmail.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/setuplib.php');

/**
 * Observers_generator test class.
 *
 * @package    tool_pluginkenobi
 * @copyright  2016 Alexandru Elisei alexandru.elisei@gmail.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_pluginkenobi_observers_generator_testcase extends advanced_testcase {

    /** @var string[] Basic recipe. */
    protected static $baserecipe = array(
        'component' => 'observersgeneratortest',
        'name'      => 'observers_generator test',
        'release'  => '0.1',
        'author'    => array(
            'name'  => 'Alexandru Elisei',
            'email' => 'alexandru.elisei@gmail.com'
        ),
        'year'      => 2016,
        'version'   => '2016121200',
        'requires'  => '2.9',
        'maturity'  => 'MATURITY_ALPHA',
        'features'  => array(
            'observers' => array(
                array(
                    'eventname' => '\core\event\something_happened',
                    'callback'  => '\observersgeneratortest\event_observer::something_happened',
                    'priority'  => 200,
                ),
                array(
                    'eventname' => '\core\event\something_else_happened',
                    'callback'  => '\observersgeneratortest\another_event_observer::something_else_happened',
                )
            )
        )
    );

    /** @var string Fixture locations. */
    protected static $fixtures;

    /**
     * Sets the fixture location.
     */
    public static function setUpBeforeClass() {
        global $CFG;

        self::$fixtures = $CFG->dirroot . '/admin/tool/pluginkenobi/tests/fixtures/observers_generator';
    }

    /**
     * Tidy up open files that may be left open.
     */
    protected function tearDown() {
        gc_collect_cycles();
    }

    /**
     * Tests a recipe with missing observers.
     */
    public function test_missing_observers() {
        $recipe = self::$baserecipe;
        unset($recipe['features']['observers']);

        $this->setExpectedException('moodle_exception');
        $generator = new tool_pluginkenobi_observers_generator($recipe, '');


    }

    /**
     * Tests a recipe with missing fields.
     */
    public function test_missing_eventname() {
        $recipe = self::$baserecipe;
        unset($recipe['features']['observers'][0]['eventname']);

        $this->setExpectedException('moodle_exception');
        $generator = new tool_pluginkenobi_observers_generator($recipe, '');
    }

     /**
     * Tests a recipe with invalid values.
     */
    public function test_invalid_eventname() {
        $recipe = self::$baserecipe;
        $recipe['features']['observers'][0]['eventname'] = 'not_a_valid_namespace';

        $this->setExpectedException('moodle_exception');
        $generator = new tool_pluginkenobi_observers_generator($recipe, '');
    }

     /**
     * Tests a recipe with invalid values.
     */
    public function test_invalid_callback() {
        $recipe = self::$baserecipe;
        $recipe['features']['observers'][0]['callback'] = '\namespace\not_a_valid_static_function';

        $this->setExpectedException('moodle_exception');
        $generator = new tool_pluginkenobi_observers_generator($recipe, '');
    }

    /**
     * Tests generating the files needed for the observers.
     */
    public function test_generate_files() {
        $recipe = self::$baserecipe;
        $targetdir = make_request_directory();

        $generator = new tool_pluginkenobi_observers_generator($recipe, $targetdir);
        $generator->generate_files();

        $eventsfile = $targetdir . '/observersgeneratortest/db/events.php';
        $this->assertFileEquals($eventsfile, self::$fixtures . '/db/events.php');

        $observerclass = $targetdir . '/observersgeneratortest/classes/event_observer.php';
        $this->assertFileEquals($observerclass, self::$fixtures . '/classes/event_observer.php');

        $observerclass = $targetdir . '/observersgeneratortest/classes/another_event_observer.php';
        $this->assertFileEquals($observerclass, self::$fixtures . '/classes/another_event_observer.php');
    }
}
