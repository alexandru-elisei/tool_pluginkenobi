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
 * File containing the tests for the db_access_generator class.
 *
 * @package    tool_pluginkenobi
 * @copyright  2016 Alexandru Elisei alexandru.elisei@gmail.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/setuplib.php');

/**
 * db_access_generator test class.
 *
 * @package    tool_pluginkenobi
 * @copyright  2016 Alexandru Elisei alexandru.elisei@gmail.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_pluginkenobi_db_access_generator_testcase extends advanced_testcase {

    /** @var string[] Basic recipe. */
    protected static $baserecipe = array(
        'component' => 'dbaccessgeneratortest',
        'name'      => 'db_access_generator test',
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
            'access' => array(
                array(
                    'name' => 'view',
                    'captype' => 'read',
                    'contextlevel' => 'CONTEXT_MODULE',
                    'archetypes' => array(
                        array('role' => 'student', 'permission' => 'CAP_ALLOW'),
                        array('role' => 'editingteacher', 'permission' => 'CAP_ALLOW'))),
                array(
                    'name' => 'addinstance',
                    'riskbitmask' => 'RISK_XSS | RISK_CONFIG',
                    'captype' => 'write',
                    'contextlevel' => 'CONTEXT_COURSE',
                    'archetypes' => array(
                        array('role' => 'manager', 'permission' => 'CAP_ALLOW')))
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

        self::$fixtures = $CFG->dirroot . '/admin/tool/pluginkenobi/tests/fixtures/db_access_generator';
    }

    /**
     * Tidy up open files that may be left open.
     */
    protected function tearDown() {
        gc_collect_cycles();
    }

    /**
     * Tests a recipe with missing capabilities.
     */
    public function test_missing_capabilities() {
        $recipe = self::$baserecipe;
        unset($recipe['features']['access']);

        $this->setExpectedException('moodle_exception');
        $generator = new tool_pluginkenobi_db_access_generator($recipe, '');


    }

    /**
     * Tests a recipe with missing fields.
     */
    public function test_missing_captype() {
        $recipe = self::$baserecipe;
        unset($recipe['features']['access'][0]['captype']);

        $this->setExpectedException('moodle_exception');
        $generator = new tool_pluginkenobi_db_access_generator($recipe, '');
    }

    /**
     * Tests a recipe with missing fields.
     */
    public function test_missing_archetypes() {
        $recipe = self::$baserecipe;
        unset($recipe['features']['access'][1]['archetypes']);

        $this->setExpectedException('moodle_exception');
        $generator = new tool_pluginkenobi_db_access_generator($recipe, '');
    }

     /**
     * Tests a recipe with invalid values.
     */
    public function test_invalid_riskbitmask() {
        $recipe = self::$baserecipe;
        $recipe['features']['access'][0]['captype'] = 'invalid';

        $this->setExpectedException('moodle_exception');
        $generator = new tool_pluginkenobi_db_access_generator($recipe, '');
    }

     /**
     * Tests a recipe with invalid values.
     */
    public function test_invalid_archetype_permission() {
        $recipe = self::$baserecipe;
        $recipe['features']['access'][0]['archetypes'][0]['permission'] = 'invalid';

        $this->setExpectedException('moodle_exception');
        $generator = new tool_pluginkenobi_db_access_generator($recipe, '');
    }

    /**
     * Tests generating a db/access.php file.
     */
    public function test_generate_file() {
        $recipe = self::$baserecipe;
        $targetdir = make_request_directory();

        $generator = new tool_pluginkenobi_db_access_generator($recipe, $targetdir);
        $generator->generate_files();

        $dbaccessfile = $targetdir . '/dbaccessgeneratortest/db/access.php';
        $this->assertFileEquals($dbaccessfile, self::$fixtures . '/access.php');
    }
}
