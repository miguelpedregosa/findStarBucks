<?php
/**
* Almacena varias pruebas sobre la clase StarBucks y StarBucksCollection.
*
* Fichero que almacena varias pruebas sobre la clase StarBucks y StarBucksCollection para testear sus funcionalidades
*
* @author Miguel A. Pedregosa <miguelpedregosa@gmail.com>
* @since 1.0
* @package StarBucks
*/

require_once 'StarBucks.php';

// Zona de pruebas de la clase StarBucks

/*
 1 Creación de objetos
*/

echo "1 Creación de objetos\n\n";
 //Creamos un objeto vacío y despúes asignamos cada uno de sus valores con el método setter creado dinámicamente gracias a la metaprogramación
$local_1 = new StarBucks();
$local_1->local_id(10001);
$local_1->local_name("MadridI");
$local_1->local_address("Gran Via 134 3C");
$local_1->local_latitude(40.419394);
$local_1->local_longitude(-3.698546);

echo $local_1;

//Creo un objeto con los valores proporcionados en su constructor
$local_2 = new StarBucks(array('local_id' => 10002, 'local_name' => "MadridII", 'local_address' => "Gran Via 320 A", "local_latitude" => 40.420055, "local_longitude" => -3.702594));
echo $local_2;


/*
2 Almacenando objetos en la base de datos
*/

echo "\n2 Almacenando objetos en la base de datos\n\n";
if ($local_1->create())
{
	echo "Almacenado objeto --> ID: ".$local_1->id()."\n";
}

if ($local_2->save())
{
	echo "Almacenado objeto --> ID: ".$local_2->id()."\n";
}

$local_3 = new StarBucks();
$datos = array('local_id' => 10003, 'local_name' => "MadridIII", 'local_address' => "Gran Via 20 D", "local_latitude" => 40.435454, "local_longitude" => -3.608590);

if ($local_3->create($datos))
{
	echo "Almacenado objeto --> ID: ".$local_3->id()."\n";
}

$local_4 = new StarBucks();
$datos = array('local_id' => 10004, 'local_name' => "MadridI", 'local_address' => "Gran Via 70 F", "local_latitude" => 40.245454, "local_longitude" => -3.895590);

if ($local_4->create($datos))
{
	echo "Almacenado objeto --> ID: ".$local_4->id()."\n";
}

/*
3 Recuperando objetos de la Base de Datos
*/

echo "\n3 Recuperando objetos de la Base de Datos\n\n";
$nuevo_local = new StarBucks();
$nuevo_local->find($local_1->id());
echo $local_1;

$nuevo_local_2 = new StarBucks();
$nuevo_local_2->find_by_local_latitude(40.420055);
echo $local_2;

$nuevo_local_3 = new StarBucks();
$nuevo_local_3->find_by_local_id(10003);
echo $local_3;


/*
4 Colecciones de objetos StarBucks
*/

echo "\n4 Colecciones de objetos StarBucks\n\n";
$todos = StarBucks::all();
echo "Todos los objetos en BD\n";
foreach ($todos as $local)
{
	echo $local->local_name();
	echo "\n";
}
echo "\nAlgun(os) objeto(s) en BD\n";
$algunos = StarBucks::all_by_local_name('MadridI');

foreach ($algunos as $local)
{
	echo $local->local_name();
	echo "\n";
}

/*
5 Distancias
*/

echo "\n5 Distancias\n\n";
$distancia = $local_1->distancia_haversin(40.420576, -3.702739);
echo "La distancia entre la localización del local y las coordenadas 40.420576, -3.702739 es de ".$distancia." metros";
echo "\n";


/*
6 Borramos todos los objetos en la tabla de la BD
*/

echo "\n6 Borramos todos los objetos en la tabla de la BD\n\n";

$todos = StarBucks::all();
foreach ($todos as $local)
{
	echo "Borrando StarBucks: ".$local->local_name();
	echo $local->delete();
	echo "\n";
}