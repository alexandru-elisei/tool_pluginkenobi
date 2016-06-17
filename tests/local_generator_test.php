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
 * File containing tests for the local_generator class.
 *
 * @package    tool_pluginkenobi
 * @copyright  2016 Alexandru Elisei alexandru.elisei@gmail.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/setuplib.php');

/**
 * local_generator test class.
 *
 * @package    tool_pluginkenobi
 * @copyright  2016 Alexandru Elisei alexandru.elisei@gmail.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_pluginkenobi_local_generator_testcase extends advanced_testcase {

    /** @var string[] Basic recipe. */
    protected static $baserecipe = array(
        'component' => 'local_localgeneratortest',
        'name'      => 'local_generator test',
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
    protected static $fixtures;

    /**
     * Sets the fixture location.
     */
    public static function setUpBeforeClass() {
        global $CFG;

        self::$fixtures = $CFG->dirroot . '/admin/tool/pluginkenobi/tests/fixtures/local_generator';
    }

    /**
     * Tidy up open files that may be left open.
     */
    protected function tearDown() {
        gc_collect_cycles();
    }

    /**
     * Tests generating an local plugin type with missing required options.
     */
    public function test_missing_options() {
        $recipe = self::$baserecipe;
        unset($recipe['author']);

        $this->setExpectedException('moodle_exception');
        $processor = new tool_pluginkenobi_processor($recipe, '');
    }

    /**
     * Tests generating a local plugin type with no extra features.
     */
    public function test_no_features() {
        $recipe = self::$baserecipe;
        $recipe['features'] = array(
            'all' => false
        );
        $targetdir = make_request_directory();

        $processor = new tool_pluginkenobi_processor($recipe, $targetdir);
        $processor->generate();

        $versionfile = $targetdir . '/localgeneratortest/version.php';
        $this->assertFileEquals($versionfile, self::$fixtures . '/version.php');

        $langfile = $targetdir . '/localgeneratortest/lang/en/local_localgeneratortest.php';
        $this->assertFileEquals($langfile, self::$fixtures . '/lang/en/local_localgeneratortest.php');
    }

    /**
     * Tests generating a local plugin type with the 'settings' feature.
     */
    public function test_with_settings() {
        $recipe = self::$baserecipe;
        $recipe['features'] = array(
            'settings' => true
        );
        $targetdir = make_request_directory();

        $processor = new tool_pluginkenobi_processor($recipe, $targetdir);
        $processor->generate();

        $versionfile = $targetdir . '/localgeneratortest/version.php';
        $this->assertFileEquals($versionfile, self::$fixtures . '/version.php');

        $langfile = $targetdir . '/localgeneratortest/lang/en/local_localgeneratortest.php';
        $this->assertFileEquals($langfile, self::$fixtures . '/lang/en/local_localgeneratortest.php');

        $settingsfile = $targetdir . '/localgeneratortest/settings.php';
        $this->assertFileEquals($settingsfile, self::$fixtures . '/settings.php');
    }

    /**
     * Tests generating a local plugin type with the 'access' feature.
     */
    public function test_with_access() {
        $recipe = self::$baserecipe;
        $recipe['features'] = array(
            'capabilities' => array(
                array(
                    'name' => 'view',
                    'captype' => 'read',
                    'contextlevel' => 'CONTEXT_MODULE',
                    'archetypes' => array(
                        array('role' => 'student', 'permission' => 'CAP_ALLOW'),
                        array('role' => 'editingteacher', 'permission' => 'CAP_ALLOW'))
                    )
                ));
        $targetdir = make_request_directory();

        $processor = new tool_pluginkenobi_processor($recipe, $targetdir);
        $processor->generate();

        $versionfile = $targetdir . '/localgeneratortest/version.php';
        $this->assertFileEquals($versionfile, self::$fixtures . '/version.php');

        $langfile = $targetdir . '/localgeneratortest/lang/en/local_localgeneratortest.php';
        $this->assertFileEquals($langfile, self::$fixtures . '/lang/en/local_localgeneratortest.php');

        $dbaccessfile = $targetdir . '/localgeneratortest/db/access.php';
        $this->assertFileEquals($dbaccessfile, self::$fixtures . '/db/access.php');
    }

    /**
     * Tests generating a local plugin type with the 'observers' feature.
     */
    public function test_with_observers() {
        $recipe = self::$baserecipe;
        $recipe['features'] = array(
            'observers' => array(
                array(
                    'eventname' => '\core\event\something_happened',
                    'callback'  => '\local_localgeneratortest\event_observer::something_happened',
                    'priority'  => 200,
                ),
                array(
                    'eventname' => '\core\event\something_else_happened',
                    'callback'  => '\local_localgeneratortest\another_event_observer::something_else_happened',
            )));
        $targetdir = make_request_directory();

        $processor = new tool_pluginkenobi_processor($recipe, $targetdir);
        $processor->generate();

        $versionfile = $targetdir . '/localgeneratortest/version.php';
        $this->assertFileEquals($versionfile, self::$fixtures . '/version.php');

        $langfile = $targetdir . '/localgeneratortest/lang/en/local_localgeneratortest.php';
        $this->assertFileEquals($langfile, self::$fixtures . '/lang/en/local_localgeneratortest.php');

        $eventsfile = $targetdir . '/localgeneratortest/db/events.php';
        $this->assertFileEquals($eventsfile, self::$fixtures . '/db/events.php');

        $observerclass = $targetdir . '/localgeneratortest/classes/event_observer.php';
        $this->assertFileEquals($observerclass, self::$fixtures . '/classes/event_observer.php');

        $observerclass = $targetdir . '/localgeneratortest/classes/another_event_observer.php';
        $this->assertFileEquals($observerclass, self::$fixtures . '/classes/another_event_observer.php');
    }

    /**
     * Tests generating a local plugin type with the 'events' feature.
     */
    public function test_with_events() {
        $recipe = self::$baserecipe;
        $recipe['features'] = array(
            'events' => array(
                array(
                    'eventname' => 'event_class',
                    'extends'  => '\core\event\something_happened'
                ),
                array(
                    'eventname' => 'another_event_class'
                )
            ));
        $targetdir = make_request_directory();

        $processor = new tool_pluginkenobi_processor($recipe, $targetdir);
        $processor->generate();

        $versionfile = $targetdir . '/localgeneratortest/version.php';
        $this->assertFileEquals($versionfile, self::$fixtures . '/version.php');

        $langfile = $targetdir . '/localgeneratortest/lang/en/local_localgeneratortest.php';
        $this->assertFileEquals($langfile, self::$fixtures . '/lang/en/local_localgeneratortest.php');

        $eventclass = $targetdir . '/localgeneratortest/classes/event/event_class.php';
        $this->assertFileEquals($eventclass, self::$fixtures . '/classes/event/event_class.php');

        $eventclass = $targetdir . '/localgeneratortest/classes/event/another_event_class.php';
        $this->assertFileEquals($eventclass, self::$fixtures . '/classes/event/another_event_class.php');
    }
}
