<?php
if (! defined( 'BASEPATH' )){
	exit( 'No direct script access allowed' );
}
$output = <<<EOF
<!DOCTYPE html>
<html lang="it" class="chrome" dir="ltr">
{$head}
{$contenuto}
</html>
EOF
;
print $output;
?>