<?php
/**
* Almacena la clase StarBucks.
*
* Fichero que almacena la clase StarBucks.
*
* @author Miguel A. Pedregosa <miguelpedregosa@gmail.com>
* @since 1.0
* @package StarBucks
*/

require_once '../settings.php';
require_once 'StarBucksCollection.php';

/**
* Clase para gestionar y almacenar en BD información sobre cafeterías StarBucks
*
* De cada cafetería StarBucks almacenamos información como su nombre, su dirección y sus coordenadas (longitud, latitud).
* Esta clase permite gestionar, almacenar y recuperar información de una cafetería en concreto o colecciones de ellas 
* mediante la clase auxiliar iterable StarBucksCollection.
* Mediante la implementación de técnicas de metaprogramación existen varias funciones que se crean "al vuelo".
* Por ejemplo, hay una función llamada find_by_campo() donde "campo" puede ser cualquier cosa. Así la función
* find_by_local_name('Plenilucio') nos devuelve los datos de la cafetería cuyo campo local_name en la base de datos
* es 'Plenilucio' sin que hayamos definido explícitamente la función "find_by_local_name".
* En el archivo StarBucks_test.php hay pruebas y ejemplos de los métodos de la clase.
*
* @author Miguel A. Pedregosa <miguelpedregosa@gmail.com>
* @since 1.0
* @package StarBucks
*/
class StarBucks
{
	/**
    * Almacena el enlace de conexión con la base de datos MySQL
    */
	private $db_connection = null;

	/**
    * Almacena la ID en base de datos del objeto
    */	
	private $id = null;

	/**
    * Almacena el identificador del local en concreto (información proporcionada por la empresa StarBucks)
    */
	private $local_id;

	/**
    * Almacena el nombre del local o cafetería
    */
	private $local_name;

	/**
    * Almacena la dirección del local o cafetería
    */
	private $local_address;

	/**
    * Almacena la latitud (coordenadas decimales) del local o cafetería
    */	
	private $local_latitude;

	/**
    * Almacena la longitud (coordenadas decimales) del local o cafetería
    */
	private $local_longitude;

	/**
    * Almacena el país del local o cafetería
    */
	private $local_country;

/**
* Establece las propiedades un objeto de la clase StarBucks
*
* Esta función privada establece las propiedades de una instancia de la clase StarBucks (a excepción de la propiedad id que controla si es objeto se ha 
* almacenado de forma permanente en la base de datos). Para cada propiedad realiza la conversión de datos correspondiente asegurando que los datos se
* almacenen con el tipo adecuado según su naturaleza.
*
* @param array $hash Array asociativo o diccionario con los valores de cada propiedad del objeto. No tienen porqué estar todos
* @return void
*/
	private function setter($hash = null)
	{
		if($hash == null)
		{
			return;
		}
		if(array_key_exists('local_id', $hash))
		{
			$this->local_id = (int) $hash['local_id'];
		}
		if(array_key_exists('local_name', $hash))
		{
			$this->local_name = $hash['local_name'];
		}
		if(array_key_exists('local_address', $hash))
		{
			$this->local_address = $hash['local_address'];
		}
		if(array_key_exists('local_latitude', $hash))
		{
			$this->local_latitude = (double) $hash['local_latitude'];
		}
		if(array_key_exists('local_longitude', $hash))
		{
			$this->local_longitude = (double) $hash['local_longitude'];
		}
		if(array_key_exists('local_country', $hash))
		{
			$this->local_country = (string) $hash['local_country'];
		}
	}

/**
* Realiza una conexión con la base de datos del proyecto
*
* Función privada que establece (o trata de ello) una conexión con la base de datos del proyecto. Usa la extensión de php mysqli en lugar de la
* extensión mysql normal si está disponible. En caso de estar mysqli disponible usará la extensión mysql normal
*/

	private function make_db_connection()
	{
		global $settings;
		//Prefiero usar la extensión mysqli si está disponible
		if (function_exists("mysqli_connect"))
		{
			$this->db_connection = new mysqli($settings['servidor'], $settings['usuario'], $settings['password'], $settings['bd']);
			if (mysqli_connect_error()) //Debería usarse $this->db_connection->connect_error pero no se garantiza que funciones en PHP < 5.3.0
			{
				$this->db_connection =  null;
			}
		}
		else
		{
			$link = mysql_connect($settings['servidor'], $settings['usuario'], $settings['password']);
			if (!$link)
			{
				$this->db_connection =  null;
			}
			else
			{
				mysql_select_db($settings['bd'], $link);
				$this->db_connection = $link;
			}
		}
	}

/**
* Realiza una consulta a la base de datos proyecto
*
* Función privada que realiza una consulta a base de datos del proyectos. Dichas consultas se usarán tanto para insertar datos (hacer permanente el objeto)
* como para recoger información de la base de datos. Trata de usar la extensión mysqli si está disponble en el sistema.
*
* @param string $sql Cadena de texto con la consulta a ejecutar.
* @return mixed Devuelve un objeto resultado de la libreria de mysqli o mysql
*/
	private function make_db_query($sql)
	{
		//Si no exite conexión a la BD trata de crearla antes de otra cosa
		if($this->db_connection == null)
		{
			$this->make_db_connection();
		}
		//Si tras tratar de crear la conexión sigue sin existir no puedo continuar con la ejecución de la consulta
		if($this->db_connection == null)
		{
			return false;
		}
		//Distingo entre si estoy usando la libreria mysqli o mysql normal
		if(get_class($this->db_connection) == 'mysqli')
		{
			return $this->db_connection->query($sql);
		}
		else
		{
			return mysql_query($sql, $this->db_connection);
		}
	}

/**
* Realiza una consulta a la base de datos (para funciones estáticas de la clase)
*
* Función que realiza una consulta a la base de datos, para ser usada en las funciones estáticas de la clase al no requerir ninguna información
* de la instancia actual del objecto StarBucks
*
* @param string $sql Cadena de texto con la consulta a ejecutar.
* @return mixed Devuelve un objeto resultado de la libreria de mysqli o mysql
*/
	private function static_make_db_query($sql)
	{
		global $settings;
		//Creo una conexion con la base de datos
		if (function_exists("mysqli_connect"))
		{
			$link = new mysqli($settings['servidor'], $settings['usuario'], $settings['password'], $settings['bd']);
			if (mysqli_connect_error())
			{
				return false;
			}
			//Ahora ejecuto la consulta y devuelvo los resultados
			$res = $link->query($sql);
			$link->close();
			return $res;

		}
		else
		{
			$link = mysql_connect($settings['servidor'], $settings['usuario'], $settings['password']);
			if(!$link)
			{
				return false;
			}
			mysql_select_db($settings['bd'], $link);
			//Ahora ejecuto la consulta y devuelvo los resultados
			$res = mysql_query($sql, $link);
			mysql_close($link);
			return $res;
		}

	}

/**
* Devuelve el último mensaje de error devuelto por MySQL
*
* Función privada que devuelve el último mensaje de error enviado por el servidor de MySQL a PHP
*
* @return string Cadena con el último mensaje de error de MySQL
*/
	private function get_mysql_error()
	{
		if(get_class($this->db_connection) == 'mysqli')
			{
				return $this->db_connection->error;
			}
			else
			{
				return mysql_error();
			}
	}

/**
* Escapa los caracteres de una cadena para que no puedan causar daños en una consulta SQL
*
* Función privada encargada de escapar los caracteres especiales de una cadena de texto para que no resulten dañinos en una consulta SQL
*
* @param string $string Cadena de texto a escapar
* @return string Cadena de texto con los caracteres escapados
*/
	private function escape_string($string)
	{
		if(get_class($this->db_connection) == 'mysqli')
		{
			return $this->db_connection->real_escape_string($string);
		}
		else
		{
			return mysql_real_escape_string($string);
		}
	}

/**
* Implementa las funciones de metaprogramación para la clase StarBucks
*
* Función mágica de PHP que se utiliza para dotar de metaprogramación a la clase StarBucks. 
* Podemos ejecutar métodos que no se han declarados explícitamente (esta función se llama cuando no se encuentra un método declarado en la clase).
* y cuyos nombres coincidan con patrones establecidos por nosotros. Estas funciones serán de tres tipos set/get para las propiedades de la clase, find_by_algo y 
* all_by_algo. El primer tipo nos permitirá tener funciones de tipo propiedad() y propiedad($valor) para leer y establecer los valores del campo "propiedad".
*
* Ejemplo: $obj->local_name() devuelve el valor del campo local_name del objeto $obj Tratar de acceder directamente no funciona (son propiedades privadas)
*
* El segundo tipo se usa para recuperar información de un objeto de la BD atendiendo a un campo
* El tercer tipo se usa para recuperar varios objetos de tipo StarBucks atendiendo al valor de un campo
*
* @param string $name Nombre de la función que ha sido llamada
* @param array $args Argumentos que se le ha pasado a la función llamada
*/
	public function __call($name, array $args)
	{
		//No quiero que se pueda acceder de esta forma a las variables que empiecen por _
		if ($name[0] == '_')
		{
			return;
		}

		if (property_exists(get_class($this), $name))
		{
			if(count($args) == 0)
			{
				$array = get_object_vars($this);
				return $array[$name];	
			}
			else
			{
				$array = array();
				$array[$name] = $args[0];
				$this->setter($array);
			}

		}

		//Segunda parte de la función: find_by_clave(valor)
		if(strstr($name, "find_by_"))
		{
			$clave = str_replace("find_by_", "", $name);
			return $this->find_by($clave, $args[0]);
		}
		
	}
/**
* Implementa las funciones de metaprogramación (en funciones estáticas) para la clase StarBucks
*
* Función analoga a la anterior para dotar de metaprogamación en funciones estáticas. No funciona en versiones anteriores a PHP 5.3
*
* @param string $name Nombre de la función que ha sido llamada
* @param array $args Argumentos que se le ha pasado a la función llamada
*/
	public static function __callStatic($name, array $args)
	{
		//Solo funciona desde PHP 5.3.0 en adelante
		//Ultima parte de la funciona mágica __call, funciones de tipo all_by_campo
		if(strstr($name, "all_by_"))
		{
			$clave = str_replace("all_by_", "", $name);
			if(isset($args[0]))
				$param0 = $args[0];
			else
				$param0 = null;
			
			if(isset($args[1]))
				$param1 = $args[1];
			else
				$param1 = null;

			if(isset($args[2]))
				$param2 = $args[2];
			else
				$param2 = null;

			if(isset($args[3]))
				$param3 = $args[3];
			else
				$param3 = null;

			return self::all_by($clave, $param0, $param1, $param2, $param3);
		}
	}

/**
* Obtiene una representación en texto de un objeto de tipo StarBucks
*
* Devuelve una cadena con los campos del objeto de tipo StarBucks para que puedan ser mostrados por pantalla.
*
* @return string Cadena con todos los datos de la instancia del objeto StarBucks
*/

	public function __toString()
	{
		return $this->local_name()." (".(string) $this->local_id().") \n".$this->local_address()." [".(string) $this->local_country()."] (".(string) $this->local_latitude().", ".(string) $this->local_longitude().")\n";
	}

/**
* Constructor de la clase StarBucks
*
* Crear una instancia de la clase StarBucks con los datos que se le pasan como parámetro (no obligatorios)
*
* @param array $hash Datos para crear la instancia del objeto StarBucks
*/

	public function __construct($hash = null)
	{
		$this->setter($hash);
	}

/**
* Crea y almacena en la base de datos el objeto de la clase StarBucks
*
* Este método crea un nuevo registro en la base de datos con los datos del objeto StarBucks. Si se le pasan estos datos como parámetro se cambian en las
* propiedades del objeto antes de ser creado en la BD. Si la instancia del objeto ya había sido guardada en BD anteriormente llama al método que permite actualizar el
* el objeto en la base de datos. Si no se puede crear el objeto en BD devuelve una excepción.
*
* @param array $hash Datos para crear la instancia del objeto StarBucks
* @return boolean True si se almacena el objeto en BD y False en caso contrario
*/

	public function create($hash = null)
	{
		//Relleno las propiedades de la instancia de la clase
		$this->setter($hash);
		
		if ($this->id != null)
		{
			return $this->save();
		}
		
		//Ahora tengo que guardar en la base de datos
		//Preparo la consulta y la ejecuto, aprovecho los métodos creados dinámicamente para obtener los valos de cada atributo.
		$sql = "INSERT INTO `starbucks` ( `local_id`, `local_name`, `local_address`, `local_country`, `local_latitude`, `local_longitude`) VALUES (";
		$sql .= "".$this->local_id().", ";
		$sql .= "'".$this->escape_string($this->local_name())."', ";
		$sql .= "'".$this->escape_string($this->local_address())."', ";
		$sql .= "'".$this->escape_string($this->local_country())."', ";
		$sql .= "".$this->local_latitude().", ";
		$sql .= "".$this->local_longitude()."";
		$sql .= ");";

		//Una vez que tengo la sql creada la ejecuto 
		$res = $this->make_db_query($sql);
		if (!$res)
		{
			$error = $this->get_mysql_error();
			throw new Exception('Error al crear objeto en BD: '.$error);
			return false;
		}

		//Guardo la id del objeto en BD en la propiedad de la instancia de este objeto
		if(get_class($this->db_connection) == 'mysqli')
		{
			$insert_id = $this->db_connection->insert_id;
		}
		else
		{
			$insert_id = mysql_insert_id();
		}
		$this->id = $insert_id;
		return true;

	}
/**
* Actualiza el objeto en BD correspondiente a la instancia del objeto StarBucks en memoria
*
* Función que actualiza el objeto en memoria correspondiente a la instancia del objeto en memoria. Si el objeto no se ha creado antes en la BD llamará a la
* función create para aseguar su correcta creación en la base de datos. Si no se puede guardar el objeto en BD devuelve una excepción.
*
* @return boolean True si se almacena el objeto en BD y False en caso contrario
*/
	public function save()
	{
		if ($this->id == null)
		{
			return $this->create();
		}

		//Preparo la consulta para actualizar los valores en la BD con los valores de la instancia y la ejecuto
		$sql = "UPDATE `starbucks` SET ";
		$sql .= "`local_id` = ".$this->local_id().", ";
		$sql .= "`local_name` = '".$this->escape_string($this->local_name())."', ";
		$sql .= "`local_address` = '".$this->escape_string($this->local_address())."', ";
		$sql .= "`local_country` = '".$this->escape_string($this->local_country())."', ";
		$sql .= "`local_latitude` = ".$this->local_latitude().", ";
		$sql .= "`local_longitude` = ".$this->local_longitude()." ";
		$sql .= "WHERE `id` = ".$this->id().";";

		//Una vez que tengo la sql creada la ejecuto 
		$res = $this->make_db_query($sql);
		if (!$res)
		{
			$error = $this->get_mysql_error();
			throw new Exception('Error al actualizar objeto en BD: '.$error);
			return false;
		}
		else
		{
			return true;
		}


	}
/**
* Lee la información de un StarBucks desde la base de datos y lo carga en la instancia del objeto en memoria.
*
* Busca en la base de datos la información del establecimiento cuyo nombre de campo establecido por $name coincide con el valor proporcionado en $value
* La información recuperada de la base de datos es cargada en la instancia actual del objeto en memoria.
* Devuelve una excepción si no encuentra el objeto a buscar.
*
* @param string $name Nombre del campo por el que se va a buscar el objeto en la base de datos
* @param string $value Valor del campo que queremos que coincida para encontrar a nuestro objeto
* @return boolean True si el registro se encuentra en la BD y false en caso contrario
*/ 
	private function find_by($name, $value)
	{
		//Construyo la SQL y la ejecuto contra la DB
		$sql = "SELECT * FROM `starbucks` WHERE ".$name." = ".$value." LIMIT 1;";
		$res = $this->make_db_query($sql);
		if (!$res)
		{
			$error = $this->get_mysql_error();
			throw new Exception('Objeto no encontrado: '.$error);
			return false;
		}
		//Obtengo los datos de la fila
		if(get_class($this->db_connection) == 'mysqli')
		{
			$datos = $res->fetch_assoc();
		}
		else
		{
			$datos = mysql_fetch_assoc($res);
		}

		$this->id = $datos['id'];
		$this->setter($datos);
		return true;
	}

/**
* Lee la información de un objeto StarBucks desde la base de datos y lo carga en memoria. Busca por la id del resgristro en la tabla
*
* Busca en la base de datos el registro en la tabla cuya id coincida con el valor pasado como parámetro y lo carga en la instancia del objeto en memoria.
*
* @param int $id ID del objeto en la tabla de la base de datos
* @return boolean True si el registro se encuentra en la BD y false en caso contrario
*/
	public function find($id)
	{
		return $this->find_by_id($id);
	}


/**
* Borra un objeto de la base de datos y sus propiedades de la instancia en memoria.
* 
* Borra el resgistro de la tabla en la base de datos, correspondiente al objeto de la instancia actual de la clase StarBucks
* Si el objeto no había sido almacenado en la base de datos solo resetea sus propiedades.
* Devuelve una excepción si el objeto no se puede borrar correctamente
*
* @return boolean True si el registro se borra correctamente y false en caso contrario
*/
	public function delete()
	{
		//Si no está guardado en la BD solo borro las propiedades del objeto en memoria
		if ($this->id() == null)
		{
			$this->id = null;
			$this->local_id = null;
			$this->local_name = null;
			$this->local_address = null;
			$this->local_latitude = null;
			$this->local_longitude = null;
			$this->local_country = null;
			return true;
		}
		//Construyo la SQL y la ejecuto contra la DB
		$sql = "DELETE FROM `starbucks` WHERE id = ".$this->id()." LIMIT 1";
		$res = $this->make_db_query($sql);
		if (!$res)
		{
			$error = $this->get_mysql_error();
			throw new Exception('Objeto no borrado: '.$error);
			return false;
		}
		else
		{
			$this->id = null;
			$this->local_id = null;
			$this->local_name = null;
			$this->local_address = null;
			$this->local_latitude = null;
			$this->local_longitude = null;
			$this->local_country = null;
			return true;
		}

	}

/**
* Crea la segunda parte de una consulta SELECT a la base de datos
*
* Crea la segunda parte de una consulta SELECT añadiendo la información sobre el límite de resultados a devolver
* y el orden en que deben hacerse.
*
* @param string $limit Número de resultados a devolver
* @param string $order Campo que se usará para ordenar los resultados a mostrar
* @param string $order_mod Modificador del orden usado, puede ser ARC o DESC
* @return string Parte de la consulta SQL con los parametros colocados correctamente
*/
	private function end_select_query($limit = null, $order = null, $order_mod = null)
	{
		$sql = '';
		if(isset($order))
		{
			$order = (string) $order;
			if(isset($order_mod))
			{
				$order_mod = (string) strtoupper($order);
				if($order_mod != "ASC" && $order_mod != "DESC")
				{
					$order_mod = "ASC";
				}
			}
			else
			{
				$order_mod = "ASC";
			}

			$sql .= " ORDER BY ".$order." ".$order_mod;
		}

		if(isset($limit) && $limit != 'all')
		{
			$limit = (string) $limit;
			$sql .= " LIMIT ".$limit;
		}
		return $sql;

	}
/**
* Devuelve todos los objetos de clase StarBucks disponibles en la base de datos
*
* Esta función devuelve una colección de objetos de tipo StarBucks, un objeto StarBucksCollection con todos los registros de la tabla en la base de datos.
* Es una función estática de la clase, no puede usarse con una instancia en concreto sino de la forma StarBucks::all() Admite modificadores como el límite de 
* resultados a devolver y el orden de los mismos.
*
* @param string $limit Número de resultados a devolver
* @param string $order Campo que se usará para ordenar los resultados a mostrar
* @param string $order_mod Modificador del orden usado, puede ser ARC o DESC
* @return StarBucksCollection Colección de objetos de tipo StarBucks con todos los objetos de la tabla en la BD
*/

	public static function all($limit = null, $order = null, $order_mod = null)
	{
		$resultados = new StarBucksCollection(); //El resultado se devuelve como una colección de objetos de tipo StarBucks
		//Construyo la consulta y la ejecuto contra la base de datos;
		$sql = "SELECT id FROM `starbucks` ";

		$sql .= self::end_select_query($limit,$order,$order_mod);
		$sql .= " ;";

		$res = self::static_make_db_query($sql);
		
		if(!$res)
		{
			throw new Exception("Error Processing Request", 1);
			return false;
			
		}

		if(get_class($res) == 'mysqli_result')
		{
			while ($fila = $res->fetch_assoc()) {
				$resultados->add($fila['id']);
			}
		}
		else
		{
			while ($fila = mysql_fetch_assoc($res))
			{
				$resultados->add($fila['id']);
			}
		}
		//Devuelvo los resultados, un objeto de tipo StarBucksCollection
		return $resultados;
	}
/**
* Devuelve todos los objetos de clase StarBucks disponibles en la base de datos que cumplan las condiciones
*
* Esta función devuelve una colección de objetos de tipo StarBucks, un objeto StarBucksCollection con los registros de la tabla en la base de datos que
* concidan con las restricciones impuestas en el parametro $where
* Es una función estática de la clase, no puede usarse con una instancia en concreto sino de la forma StarBucks::all() Admite modificadores como el límite de 
* resultados a devolver y el orden de los mismos.
*
* @param string $where Modificador para buscar los objetos que nos interesen
* @param string $limit Número de resultados a devolver
* @param string $order Campo que se usará para ordenar los resultados a mostrar
* @param string $order_mod Modificador del orden usado, puede ser ARC o DESC
* @return StarBucksCollection Colección de objetos de tipo StarBucks con todos los objetos de la tabla en la BD
*/
	public static function where($where, $limit = null, $order = null, $order_mod = null)
	{
		$resultados = new StarBucksCollection(); //El resultado se devuelve como una colección de objetos de tipo StarBucks
		//Construyo la consulta y la ejecuto contra la base de datos;
		$sql = "SELECT id FROM `starbucks` ";
		$sql .= " WHERE ".$where;

		$sql .= self::end_select_query($limit,$order,$order_mod);
		$sql .= " ;";

		$res = self::static_make_db_query($sql);
		
		if(!$res)
		{
			throw new Exception("Error Processing Request", 1);
			return false;
			
		}

		if(get_class($res) == 'mysqli_result')
		{
			while ($fila = $res->fetch_assoc()) {
				$resultados->add($fila['id']);
			}
		}
		else
		{
			while ($fila = mysql_fetch_assoc($res))
			{
				$resultados->add($fila['id']);
			}
		}
		//Devuelvo los resultados, un objeto de tipo StarBucksCollection
		return $resultados;

	}
/**
* Devuelve todos los objetos de clase StarBucks disponibles en la base de datos que cumplan las condiciones
*
* Esta función devuelve una colección de objetos de tipo StarBucks, un objeto StarBucksCollection con los registros de la tabla en la base de datos cuyo campo
* $campo sea igual $valor
* Es una función estática de la clase, no puede usarse con una instancia en concreto sino de la forma StarBucks::all() Admite modificadores como el límite de 
* resultados a devolver y el orden de los mismos.
*
* @param string $campo Modificador para buscar los objetos que nos interesen
* @param string $valor Valor para el campo por el que queremos buscar los objetos que nos interesen
* @param string $limit Número de resultados a devolver
* @param string $order Campo que se usará para ordenar los resultados a mostrar
* @param string $order_mod Modificador del orden usado, puede ser ARC o DESC
* @return StarBucksCollection Colección de objetos de tipo StarBucks con todos los objetos de la tabla en la BD
*/
	private static function all_by($campo, $valor, $limit = null, $order = null, $order_mod = null)
	{
		$where = $campo." = '".$valor."'";
		return self::where($where, $limit, $order, $order_mod);
	}

/**
* Calcula la distancia (formula de Haversin) entre el local StarBucks y las coordenadas proporcionadas
*
* Calcula la distancia en metros entre el local almacenado en la instancia del objeto StarBucks y las coordenadas que se pasan como parámetro a la función
*
* @param double $lat2 Latitud del punto con el que queremos calcular la distancia
* @param double $long2 Longitud del punto con el que queremos calcular la distancia
* @return double Distancia entre el local StarBucks y las coordenadas proporcionadas
*/


	public function distancia_haversin($lat2, $long2)
	{
		
		if($this->local_latitude() == null || $this->local_longitude() == null)
		{
			return false;
		}

		$lat1 = $this->local_latitude();
		$long1 = $this->local_longitude();

		$R = (float)6371;
		$dLat = deg2rad($lat2 - $lat1);
		$dLong = deg2rad($long2 - $long1);
	
		$a = (sin($dLat/2) * sin($dLat/2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * (sin($dLong/2)*sin($dLong/2));
		$c = 2 * atan2(sqrt($a), sqrt(1-$a));
		//$c = 2 * asin(sqrt($a));
		$d = $R * $c;
		return ($d*1000);
	}
 

//Fin de la clase
}