<?php

/*
 * This file is a part of the Lorhondel project.
 *
 * Copyright (c) 2021-present Valithor Obsidion <valzargaming@gmail.com>
 */

$rescue = \Lorhondel\VarLoad("_globals", "RESCUE.php"); //Check if recovering from a fatal crash
if ($rescue == true) { //Attempt to restore crashed session
	echo "[RESCUE START]" . PHP_EOL;
	$rescue_dir = __DIR__ . '/_globals';
	$rescue_vars = scandir($rescue_dir);
	foreach ($rescue_vars as $var) {
		$backup_var = \Lorhondel\VarLoad("_globals", "$var");
				
		$filter = ".php";
		$value = str_replace($filter, "", $var);
		$GLOBALS["$value"] = $backup_var;
		
		$target_dir = $rescue_dir . "/" . $var; echo $target_dir . PHP_EOL;
		unlink($target_dir);
	}
	\Lorhondel\VarSave("_globals", "rescue.php", false);
	echo "[RESCUE DONE]" . PHP_EOL;
}
?>