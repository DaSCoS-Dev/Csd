<?php
if (! defined ( 'BASEPATH' )) {
	exit ( 'No direct script access allowed' );
}
class Exampletable extends main_object {
	const model_name = "model_exampletable";
	
	public function __construct($super_lib = null) {
		parent::__construct ( $super_lib );
	}
}
?>