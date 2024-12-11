<?php 
include ('dumper.php');

try {
	$world_dumper = Shuttle_Dumper::create(array(
		'host' => 'mysql',
		'username' => 'root',
		'password' => '',
		'db_name' => 'everyware',
	));

	// dump the database to gzipped file
	$world_dumper->dump('world.sql.gz');

	// dump the database to plain text file
	$world_dumper->dump('world.sql');


	

} catch(Shuttle_Exception $e) {
	echo "Couldn't dump database: " . $e->getMessage();
}
