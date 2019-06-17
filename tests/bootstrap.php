<?php
$dir = getcwd();
spl_autoload_register(function($class) use ($dir) {

	$parts = explode('\\', ltrim($class, '\\'));
	if ($parts[0] === 'Insphpect' && $parts[1] == 'StaticAnalysis') {
		array_shift($parts);
		array_shift($parts);
		require_once $dir . '/src/' . implode(DIRECTORY_SEPARATOR, $parts) . '.php';
	}
});