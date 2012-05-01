<?php
require_once '../classes/StarBucks.php';
require_once 'simplehtmldom/simple_html_dom.php';

function starbucks_crawler($local_id)
{
	$local_url = "http://www.starbucks.com/store/";

	//$local_id = 2228;
	echo "\nExplorando URL ".$local_url.$local_id."/"."\n";
	$html = file_get_html($local_url.$local_id."/");
	if(is_object($html))
	{

		if ($html->find('#page_static_error', 0) != null)
		{
			return true;
		}

		if (!method_exists($html->find("#store", 0), "children"))
		{
			return false;
		}

		$data = $html->find("#store", 0)->children(1);
		$local_name =  trim(strip_tags($data));
		
		if(strstr($local_name, "Closed"))
		{
			return true;
		}

		$data = $html->find(".street-address", 0);
		$street_address = trim(strip_tags($data));

		$data = $html->find(".extended-address", 0);
		$extended_address = trim(strip_tags($data));

		$data = $html->find(".locality", 0);
		$locality = trim(strip_tags($data));

		$data = $html->find("span[class=region]", 0);
		$region = trim(strip_tags($data));

		$data = $html->find(".postal-code", 0);
		$postal_code = trim(strip_tags($data));

		$data = $html->find(".country-name", 0);
		$country_name = trim(strip_tags($data));
		$local_country = $country_name;
		$local_address = $street_address." ".$extended_address." ".$locality." ".$region." ".$postal_code." ".$country_name;

		echo "\nDatos obtenidos\n";
		
		echo $local_name."\n";
		echo $local_address."\n";

		
		$data = $html->find("#map_holder", 0)->find('noscript', 0)->find('img',0);
		//echo $data->src;
		preg_match_all("/c=(-?)([0-9]+)\.([0-9]+),(-?)([0-9]+)\.([0-9]+)/", $data->src, $coincidencias, PREG_SET_ORDER);

		if (isset($coincidencias[0][0]))
		{
			$coordenadas = str_replace("c=", "", $coincidencias[0][0]);
			$partes = explode(',', $coordenadas);
			//print_r($partes);
			$local_latitude = (double) $partes[0];
			$local_longitude = (double) $partes[1];


		}
		else
		{
			$local_latitude = 0;
			$local_longitude = 0;

		}

		echo $local_latitude."\n";
		echo $local_longitude."\n";

		$st = new StarBucks();
		$st->find_by_local_id($local_id);

		$st->local_id($local_id);
		$st->local_name($local_name);
		$st->local_address($local_address);
		$st->local_country($local_country);
		$st->local_latitude($local_latitude);
		$st->local_longitude($local_longitude);
		
		$st->save();
		echo "\nLocal guardado en Base de datos\n";
		echo $st;
		return true;


	}
	else
	{
		return false;
	}
}

$errores = 0;
$errores_values = array();

if(isset($argv[1]))
{
	$registro_inicial = (int)$argv[1];
}
else
{
	$registro_inicial = 1;
}
if(isset($argv[2]))
{
	$registro_final = (int) $registro_inicial+$argv[2];
}
else
{
	$registro_final = 20000;
}

	
//Vamos a por los 200 primeros a ver que tal se porta o si peta
for ($i = $registro_inicial; $i<$registro_final; $i++)
{
	$porcentaje = ceil(($i / ($registro_final - $registro_inicial)) * 100);
	echo "=== Trabajo ".$i." de ".($registro_final - $registro_inicial)." (".$porcentaje."%) ===\n\n\n";
	if(starbucks_crawler($i))
	{
		echo "\nSiguiente registro\n";
		sleep(2);
	}
	else
	{
		echo "\nRepitiendo exploracion\n";
		sleep(5);
		$errores++;
		$errores_values[]=$i;
		$contador = 0;
		while(!starbucks_crawler($i) && $contador < 5)
		{
			sleep(5);
			$contador++;
		}
		sleep(2);		

	}
}	

echo "\nErrores encontrados: ".$errores."\n";
print_r($errores_values);
