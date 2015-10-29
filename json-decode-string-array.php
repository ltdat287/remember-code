<?php
$a = array(
	'name' => 'tiendat',
	'email' => 'ltdat287@gmail.com',
	'phone' => '0973587301',
	);
$a_encode = json_encode($a);
if (!file_exists('tiendat.json')) {
	touch('tiendat.json');
}
file_put_contents('tiendat.json', $a_encode);

// String after decode need second argument is true to convert ARRAY
$read_array = json_decode($a_encode, true);
$read_json = json_decode(file_get_contents('tiendat.json'));
// var_dump($read_array); die();
echo '<pre>';
print_r($read_array);
echo '</pre>';
?>