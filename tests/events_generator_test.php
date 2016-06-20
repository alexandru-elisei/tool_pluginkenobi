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
 * File containing the tests for the events_generator class.
 *
 * @package    tool_pluginkenobi
 * @copyright  2016 Alexandru Elisei alexandru.elisei@gmail.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/setuplib.php');

/**
 * Events_generator test class.
 *
 * @package    tool_pluginkenobi
 * @copyright  2016 Alexandru Elisei alexandru.elisei@gmail.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_pluginkenobi_events_generator_testcase extends advanced_testcase {

    /** @var string[] Basic recipe. */
    protected static $baserecipe = array(
        'component' => 'eventsgeneratortest',
        'name'      => 'events_generator test',
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
            'events' => array(
                array(
                    'eventname' => 'event_class',
                    'extends'  => '\core\event\something_happened'
                ),
                array(
                    'eventname' => 'another_event_class'
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

        self::$fixtures = $CFG->dirroot . '/admin/tool/pluginkenobi/tests/fixtures/events_generator';
    }

    /**
     * Tidy up open files that may be left open.
     */
    protected function tearDown() {
        gc_collect_cycles();
    }

    /**
     * Tests a recipe with missing events.
     */
    public function test_missing_events() {
        $recipe = self::$baserecipe;
        unset($recipe['features']['events']);

        $this->setExpectedException('moodle_exception');
        $generator = new tool_pluginkenobi_events_generator($recipe, '');


    }

    /**
     * Tests a recipe with missing fields.
     */
    public function test_missing_eventname() {
        $recipe = self::$baserecipe;
        unset($recipe['features']['events'][0]['eventname']);

        $this->setExpectedException('moodle_exception');
        $generator = new tool_pluginkenobi_events_generator($recipe, '');
    }

     /**
     * Tests a recipe with invalid values.
     */
    public function test_invalid_extends_class() {
        $recipe = self::$baserecipe;
        $recipe['features']['events'][0]['extends'] = 'not_a_valid_namespace';

        $this->setExpectedException('moodle_exception');
        $generator = new tool_pluginkenobi_events_generator($recipe, '');
    }

    /**
     * Tests generating the files needed for the events.
     */
    public function test_generate_files() {
        $recipe = self::$baserecipe;
        $targetdir = make_request_directory();

        $generator = new tool_pluginkenobi_events_generator($recipe, $targetdir);
        $generator->generate_files();

        $eventclass = $targetdir . '/eventsgeneratortest/classes/event/event_class.php';
        $this->assertFileEquals(self::$fixtures . '/classes/event/event_class.php', $eventclass);

        $eventclass = $targetdir . '/eventsgeneratortest/classes/event/another_event_class.php';
        $this->assertFileEquals(self::$fixtures . '/classes/event/another_event_class.php', $eventclass);
    }
}
