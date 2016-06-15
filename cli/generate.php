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
require_once(__DIR__ . '/../classes/recipe_processor.php');

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
\$php generate.php --recipe=example_recipe.yaml --targetdir=~/example

";

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    echo $help;
    die();
}

// 'recipe' is a required argument.
if (empty($options['recipe'])) {
    echo("\nRecipe not specified!\n");
    echo $help;
    die();
} else {
    $recipefile = $options['recipe'];
    // Expanding '~' on Unix-like OS'es.
    if ($recipefile[0] === '~') {
        $homedir = getenv('HOME');
        $recipefile = $homedir . substr($recipefile, 1);
    }

    $recipefile = realpath($options['recipe']);
    if ($recipefile === false) {
        echo("\nInvalid recipe file!\n");
        echo $help;
        die();
    }
}

$targetdir = null;
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
}

$options = tool_pluginkenobi_recipe_processor::load($recipefile);
$processor = new tool_pluginkenobi_processor($options, $targetdir);
$processor->generate();
