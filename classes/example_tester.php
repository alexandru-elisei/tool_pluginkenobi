<?php

define('CLI_SCRIPT', true);

require_once(__DIR__ . '/../../../../config.php');
require_once(__DIR__ . '/processor.php');

$processor = new tool_pluginkenobi_processor('', '', 'example_recipe');
$processor->generate();
