<?php
	$ARCurrent->nolangcheck=true;
	if (!$this->validateFormSecret()) {
		error($ARnls['ariadne:err:invalidsession']);
		exit;
	}
	if ($this->CheckLogin("layout") && $this->CheckConfig()) {
		$this->call("system.save.language.php", $arCallArgs);
		if (!$this->error) {
			$this->call("window.close.objectadded.js");
		} else {
			echo "<font color='red'>$this->error</font>";
		}
	}
?>
