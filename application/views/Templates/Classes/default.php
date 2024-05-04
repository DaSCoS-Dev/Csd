<?php
if (! defined( "BASEPATH" )) {
	exit( "No direct script access allowed" );
}
$class_code = <<<EOF

<?php
if (! defined ( 'BASEPATH' )) {
	exit ( 'No direct script access allowed' );
}
class {$library_name_U} extends main_object {
	const model_name = "model_{$library_name_L}";
	
	public function __construct(\$super_lib = null) {
		parent::__construct ( \$super_lib );
	}
}
?>

EOF
;
print $class_code;
?>