<?php

ini_set('error_reporting',E_ALL);
ini_set('display_startup_errors',1);
ini_set('display_errors',1);
error_reporting(E_ALL);



class Config {
	
	static $_config;

	static function forKey($key){
		global $_config;
		if($_config != "") return $_config; // File overidden.

		if(!self::$_config){
			self::$_config = Loader::loadConfig();
		}
		return self::$_config[$key];
	}
}

class Context {
	static function isCLI(){
		 return (php_sapi_name() === 'cli');
	}


	static function run(){



		$class = Router::getClass();
		$method = Router::getMethod();
		$arguments = Router::getArgs();

		if(!class_exists($class)){
			if(file_exists('controllers/'.$class.'.php')){
				include_once('controllers/'.$class.'.php');
			}
		}
		if($method == "") $method = "index";
		$c = new $class;
		$c->_call($method,$arguments);
	}
}

class Controller {

	function _call($method,$args=""){
		if (method_exists($this, $method)){
			call_user_func_array(array($this,$method),$args);
		} else {
			echo('Method named: \''.$method.'\' doesn\'t exist');
		}
	}

	function index(){ // override this
		echo "index";
	}

	function output($type="json",$data="",$view="",$as_var=false,$options){
			return $this->json_output($data,$as_var,$options);
	}

	function json_output($data,$as_var=false,$options=JSON_NUMERIC_CHECK+JSON_UNESCAPED_SLASHES){
		$out = json_encode($data,$options);
		if($as_var) return $out;
		echo $out;
	}

	function display($view,$data="",$as_var=""){	
		View::render($view,$data,$as_var);		
	}
}

class View {
	static function render($view,$data="",$as_var=""){
		return Loader::loadView($view.'.php',$data);
	} 
}

class Request {
	static function current_file(){
		$file= debug_backtrace()[1]['file'];
		$file = explode("/",$file);
		foreach($file as $segment){
			if(strtolower(substr($segment,-4)) == '.php') {
				$class = substr($segment,0,stripos($segment,".php"));
				return ucwords(strtolower($class));
			}
		}
	}

	static function method(){
		return Input::first();
	}

	static function args(){
		return Input::remainder_after_first();
	}
}

class Input {

	static function first(){
		$arg = self::allArgs(0);
		return $arg;
	}

	static function second(){
		$arg = self::allArgs(1);
		return $arg;
	}

	static function third(){
		$arg = self::allArgs(2);
		return $arg;
	}

	static function remainder_after_first(){
		$args = self::allArgs();
		$len = count($args);
		$params = array();
		for($i=1;$i<$len;$i++){
			array_push($params,$args[$i]);
		}
		return $params;
	}

	static function allArgs($index=-1){
		if(Context::isCLI()){
			if($index === -1) return CLI::enumeratedArgs();
			return isset($args[$index]) ? $args[$index] : false;
		} else {
			if($index === -1) return URL::enumeratedArgs();
			$args = URL::enumeratedArgs();
			return isset($args[$index]) ? $args[$index] : false;
		}
	}
}

class Loader {
	static function loadConfig(){
		if(file_exists('config/config.php')){
			include_once('config/config.php');
			return $_config;
		}
	}
	static function loadView($view,$data,$asVar=false){
		extract($data);

		if($asVar){
			ob_start();
			include_once('views/'.$view);
			$myvar = ob_get_clean();
			return $myvar;
		}
		include_once('views/'.$view);	
	}
}

class Router {

	static $routes = array();

	public static function routes(){
		if(count(self::$routes) > 0) return self::$routes;
		self::$routes = Config::forKey('_routes');
		return self::$routes;
	}

	public static function routeElementsForKey($key){
		$routes = self::routes();

		if(isset($routes[$key])){
			$route = explode("/",ltrim($routes[$key],"/"));
			return $route;
		}
		if($directives = self::directive()){
			$i = 0;
			$route = array();
			foreach($directives as $directive){
				$i++;
				array_push($route,$directive);
				if($i > 1) break;
			}
			return $route;
		} 

	
	}


	public static function directive(){
		$directive = "";
		if(Context::isCLI()){
			$directive = CLI::firstArg();
		} else {
			$directive = URL::firstArg();
		}
		return explode("/",$directive) ;
	}

	public static function key(){
		$stack = array();
		if(Context::isCLI()){
			$stack = explode("/",CLI::firstArg());
		} else {
			$stack = explode("/",URL::firstArg());
		}
		$key = $stack[0];
		return $key;	
	}

	public static function getClass(){
		return self::routeElementsForKey(self::key())[0];
	}

	public static function getMethod(){
		return self::routeElementsForKey(self::key())[1];
	}

	public static function getArgs(){
		$args = array();
		$elements = self::routeElementsForKey(self::key());
		$directive = self::directive();
		$_dcount = count($directive);
		$_argcount = $_dcount - 1;

		for($i=$_argcount;$i<$_dcount;$i++){
			$args[] = $directive[$i];
		}
		return $args;
	}
}


class URL {

	static function uri(){
		return $_SERVER['REQUEST_URI'];
	}

	static function fullurl(){
		return URL::protocol().'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	}

	static function https(){
		return !empty($_SERVER['HTTPS']) ? true : false;
	}

	static function protocol(){
		return self::https() ? 'https' : 'http';
	}

	static function args(){
		$q = ltrim($_SERVER['QUERY_STRING'],"/");
		parse_str($q,$out);
		return $out;
	}

	static function enumeratedArgs(){
		$args = self::args();
		$out = array();
		foreach($args as $key=>$arg){
			if($arg != ""){
				$out[] = array($key=>$arg);
			} else {
				$out[] = $key;
			}
		}
		return $out;
	}

	static function firstArg(){
		$args = self::enumeratedArgs();
		return ltrim(rtrim($args[0],"/"),"/");
	}
}

class CLI {

	static function args($ndx=-1){
		global $argv;
		return ($ndx === -1) ? $argv : $argv[$ndx];
	}


	static function firstArg(){
		$args = self::args();
		if(count($args) > 1){
			return ltrim(rtrim($args[1],"/"),"/");
		}
	}

	static function file(){
		return self::args(0);
	}

	static function enumeratedArgs(){
			$len = count(self::args());
			$args = array();
			for($i = 1; $i < $len; $i++){
				$args[] = $argv[$i]; 
			}
	}
}



// MODEL LAYER






// DATABASE LAYER

class Store extends M2Object {

}


class JSONStore extends Store {

	public static function fetch($class,$identifier){
		$data = json_decode(file_get_contents('__data/'.$class.'.json'),JSON_NUMERIC_CHECK);
		return isset($data[$identifier]) ? $data[$identifier] : false;
	}

	public static function put($class,$identifier,$data){
		$dstore = json_decode(file_get_contents('__data/'.$class.'.json'),JSON_NUMERIC_CHECK);
		$dstore[$identifier] = $data; 

		file_put_contents('__data/'.$class.'.json',json_encode($dstore,JSON_UNESCAPED_SLASHES+JSON_NUMERIC_CHECK+JSON_PRETTY_PRINT));
	}

	public static function log($class,$identfier,$data){
		if(!is_scalar($data)){
			$data = json_encode($data,JSON_UNESCAPED_SLASHES+JSON_NUMERIC_CHECK);
		}

		$buf = time().' | '.$class.'.'.$identfier.' | '.$data."\n";

		$handle = fopen('__data/log', 'a') or die('Cannot open file:  '.$my_file);
		fwrite($handle, $buf);
		fclose($handle);
	}

	function setAword($aword=""){
		echo $aword;
	}
}



// FOUNDATION

class M2Object {

	function takeValueForKey($value,$key){
		$setter = 'set'.ucwords(strtolower($key));
		p($setter);

		if(method_exists($this, $setter)
    && is_callable(array($this, $setter)))
		{
	    	call_user_func(array($this->$setter, $key));
		}
	}
}




// FUNCTIONS //////////////////////////////====================


function file_context_from_path($path){
        $fsegs = explode(DIRECTORY_SEPARATOR, $path);
        $seg_count = count($fsegs);
        $file_context = "";
        for ($i = $seg_count - 2; $i < $seg_count; $i++) {
            $file_context.= DIRECTORY_SEPARATOR . $fsegs[$i];
        }

        return $file_context;
}



function debug_context($asString=true)
{
        // Build the caller context
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $data['cfile_context'] = file_context_from_path($backtrace[1]['file']);
        $data['cclass'] = & $backtrace[1]['class'];
        $data['cfunction'] = & $backtrace[1]['function'];
        $data['ctype'] = & $backtrace[1]['type'];
        $data['cline'] = $backtrace[1]['line'];
        if(!$asString) return $data;  


        $context = $data['cclass'] . 
                $data['ctype'] . 
                $data['cfunction'] . 
                ' [' . 
                $data['cfile_context'] . 
                ':' . 
                $data['cline'] . 
                ']';


        return $context;
}



function p($object)
{
        echo "<pre>\n" . debug_context() . " \n\n";
 
        print_r($object);
        echo "\n__________________________________________________________________\n";
        echo "</pre>";
}










