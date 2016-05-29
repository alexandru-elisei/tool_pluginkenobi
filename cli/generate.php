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
 * CLI script for generating a plugin.
 *
 * @package    tool_pluginkenobi
 * @copyright  2016 Alexandru Elisei
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../../config.php');
require_once($CFG->libdir . '/clilib.php');
require_once(__DIR__ . '/../classes/processor.php');

// Now get cli options.
list($options, $unrecognized) = cli_get_params(array(
    'recipe' => '',
    'targetdir' => '',
    'help' => '',
),
array(
    'r' => 'recipe',
    't' => 'targetdir',
));

$help =
"\nGenerate a Moodle plugin.

Options:
-r, --recipe               Recipe file location
-t, --targetdir            Target directory

Example:
\$php generate.php --recipe=example_recipe.yaml --target=~/example

";

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    echo $help;
    die();
}

// Right now 'recipe' is a required argument, this will change.
if (empty($options['recipe'])) {
    echo("\nRecipe not specified!\n");
    echo $help;
    die();
} else {
    $recipelocation = $options['recipe'];
    // Expanding '~' on Unix-like OS'es.
    if ($recipelocation[0] === '~') {
        $homedir = getenv('HOME');
        $recipelocation = $homedir . substr($recipelocation, 1);
    }

    $recipelocation = realpath($options['recipe']);
    if ($recipelocation === false) {
        echo("\nInvalid recipe file!\n");
        echo $help;
        die();
    }
}

if (!empty($options['targetdir'])) {
    $targetdir = $options['targetdir'];
    // Expanding '~' on Unix-like OS'es.
    if ($targetdir[0] === '~') {
        $homedir = getenv('HOME');
        $targetdir = $homedir . substr($targetdir, 1);
    }

    $targetdir = realpath($targetdir);
    if ($targetdir === false) {
        echo("\nInvalid target directory!\n");
        echo $help;
        die();
    }

    // Target directories must end with a '/'.
    if ($targetdir[strlen($targetdir) - 1] != '/') {
        $targetdir = $targetdir . '/';
    }
} else {
    $targetdir = null;
}

if (empty($options['recipe'])) {
    $plugintype = $options['plugintype'];
    unset($options['plugintype']);
    $processor = new tool_pluginkenobi_processor($plugintype, $options, null, $targetdir);
} else {
    $processor = new tool_pluginkenobi_processor('', array(), $recipelocation, $targetdir);
}

$processor->generate();
