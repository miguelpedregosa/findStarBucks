<?php
require_once '../tools/krumo/class.krumo.php';
require_once 'StarBucks.php';


$algunos = StarBucks::nearest_places(37.17967, -3.59965);

foreach ($algunos as $local)
{
	echo $local;
	//echo "\n";
	echo "Distancia : ".(string) round(($local->distancia_haversin(37.17967, -3.59965) / 1000),2). " Km\n\n";
}