<?php
/**
* Almacena la clase StarBucksCollection.
*
* Fichero que almacena la clase StarBucksCollection.
*
* @author Miguel A. Pedregosa <miguelpedregosa@gmail.com>
* @since 1.0
* @package StarBucks
*/

require_once "StarBucks.php";

/**
* Clase para gestionar colecciones de objetos de tipo StarBucks
*
* Esta clase nos permite almacenar e iterar sobre colecciones de objetos de tipo StarBucks. Se usa normalmente para devolver conjuntos 
* de resultados de la Base de Datos de forma que se pueda iterar sobre ellos fácilmente.
* Implementa la interfaz Iterator de PHP
* 
* @author Miguel A. Pedregosa <miguelpedregosa@gmail.com>
* @since 1.0
* @package StarBucks
*/
class StarBucksCollection implements Iterator
{
	/**
    * Almacena los objetos de la colección StarBucksCollection
    */
    private $items = array();

    /**
    * Almacena la positición actual sobre la estamos iterando la colección de objetos
    */
	private $position = 0;

/**
* Crea un nuevo objeto de tipo StarBucksCollection
*
* Constructor de la clase, crea un nuevo objeto de la clase StarBucksCollection para almacenar un conjunto de resultado
*
*/
	public function __construct() {
        $this->position = 0;
        $this->items = array();
    }

/**
* Devuelve el elemento actual en una iteración sobre la colección
*
* Función del iterador que devuelve el elemento (de clase StarBucks) sobre el que nos encontramos iterando actualmente.
*
* @return StarBucks Objeto actual sobre el que estamos iterando
*/
    public function current()
    {
    	$obj = new StarBucks();
    	$obj->find_by_id($this->items[$this->position]);
    	return $obj;
    }

/**
* Devuelve la posición del elemento actual sobre el que estamos iterando.
*
* Función del iterador que devuelve la clave o posición del elemento actual de la colección sobre el que estamos iterando.
*
* @return int Posición del elemento actual de la colección.
*/
    public function key()
    {
    	return $this->position;
    }

/**
* Avanza el contardor que indica la posición actual de la iteración sobre la colección de objetos
*
* Función del iterador que aumenta la posición en la que nos encontramos sobre la colección de objetos.
*/
    public function next()
    {
    	++$this->position;
    }

/**
* Mueve el inidicador de la posición actual al inicio de la colección
*
* Función del iterador que mueve el indicador de posición actual al primer elemento de la colección de objetos.
*/
    public function rewind()
    {
    	$this->position = 0;
    }

/**
* Indica si el elemento actual de la iteración es un objeto establecido en la colección
*
* Función del iterador que indica si la posición actual sobre la que estamos iterando se corresponde con un objeto almacenado en la colección o ya nos hemos salido del
* rango de objetos disponibles en la colección.
*
* @return boolean True si la posición actual corresponde a un objeto de la colección, False en caso contrario
*/
    public function valid()
    {
    	return isset($this->items[$this->position]);
    }

/**
* Almacena un objeto de tipo StarBucks en la colección de objetos
*
* Introduce un nuevo objeto en la colección, añadiendo el objeto al final de la misma.
*
* @param mixed $item Objeto a introducir en la colección
*/
    public function push($item)
    {
    	
    	if (is_object($item) && get_class($item) == 'StarBucks')
    	{
    		$id = $item->id();
    	}
    	else
    	{
    		$id = (int) $item;
    	}

    	array_push($this->items, $id);
    	$this->items = array_unique($this->items);

    }

/**
* Almacena un objeto de tipo StarBucks en la colección de objetos (alias para la función push)
*
* Introduce un nuevo objeto en la colección, añadiendo el objeto al final de la misma.
*
* @param mixed $item Objeto a introducir en la colección
*/ 
    public function add ($item)
    {
    	return $this->push($item);
    }

/**
* Extrae el último elemento introducido en la colección de objetos
*
* Devuelve el último elemento de la colección y lo extrae de la misma (funciona como una pila)
*
* @return StarBucks Último objeto introducido en la colección
*/
    public function pop()
    {
    	$id = array_pop($this->items);
    	$obj = new StarBucks();
    	$obj->find_by_id($id);
    	return $obj;
    }

/**
* Devuelve el primer elemento introducido en la colección de objetos
*
* Devuelve el primer elemento de la colección, no se modifica la colección de objetos
*
* @return StarBucks Primer objeto introducido en la colección
*/
    public function first()
    {
    	$id = reset($this->$items);
    	$obj = new StarBucks();
    	$obj->find_by_id($id);
    	return $obj;
    }

/**
* Devuelve el último elemento introducido en la colección de objetos
*
* Devuelve el último elemento de la colección, no se modifica la colección de objetos
*
* @return StarBucks Último objeto introducido en la colección
*/
    public function last()
    {
    	$id = end($this->$items);
    	$obj = new StarBucks();
    	$obj->find_by_id($id);
    	return $obj;
    }

/**
* Devuelve el objeto que se encuentra en la posición que se le indica
*
* Devuelve el objeto StarBucks que se encuentra en la posición que se le pasa como parametro, si existe dicho objeto.
*
* @param int $position Posición del objeto que queremos obtener
* @return StarBucks Objeto que se encuentra en la posición solicitada. False en caso de que no se le haya pasado una posición válida.
*/
    public function get($position = 0)
    {
    	if (isset($this->items[position]))
    	{
    		$id = $this->items[position];
    		$obj = new StarBucks();
			$obj->find_by_id($id);
    		return $obj;
    	}
        else
        {
            return false;
        }
    }

/**
* Devuelve el número de elementos que tiene la colección de objetos
*
* Devuelve el número de objetos de tipo StarBucks que tenemos almacenados en la colección actualmente.
*
* @return int Número de elementos de la colección
*/
    public function count()
    {
    	return count($this->items);
    }

	
}