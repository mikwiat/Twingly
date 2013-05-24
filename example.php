<?php

	require_once('twingly.php');
	
	$twingly = new Twingly();
	$items = $twingly->setQ('iPhone 4s')->getResult();
	
	print_r($items);


	echo "Trlalalallalllla";
	echo "Tjenis";
?>
