<?php

	/**
	 * Simple class for getting twingly-data through the RSS
	 * 
	 * @author Michal Kwiatkowski <mikwiat@gmail.com> 
	 */
	
	class Twingly {
		
		// twinglies own params
		protected $q;
		protected $lang;

		private $url;
		
		public function __construct() {
			$this->url = 'http://www.twingly.com/search.rss?';
		}
		
		public function __call($name, $arguments) {
			// Checking if method exist
			if(!method_exists($this, $name)) {
				// set or get?
				if($this->getMethodPrefix($name) == 'set') {
					if(in_array($this->getMethodSuffix($name), $this->getProperties('PROTECTED'))) {
						$this->{$this->getMethodSuffix($name)} = $arguments;
						return $this;
					} else {
						throw new Exception('Can not set value for property: ' . $this->getMethodSuffix($name) . '. Property is not protected.');
					}
				} elseif($this->getMethodPrefix($name) == 'get') {
					if(in_array($this->getMethodSuffix($name), $this->getProperties('PROTECTED'))) {
						return array_shift($this->{$this->getMethodSuffix($name)});
					} else {
						throw new Exception('Can not get value of property: ' . $this->getMethodSuffix($name) . '. Property is not protected.');
					}
				}		
			}
		}
	
		/**
		 * getting class method prefix
		 * 
		 * @param string $name
		 * @return string 
		 */
		private function getMethodPrefix($name) {
			return strtolower(substr($name, 0, 3));
		}
		
		/**
		 * getting class method suffix
		 * 
		 * @param string $name
		 * @return string 
		 */
		private function getMethodSuffix($name) {
			return strtolower(substr($name, 3, strlen($name) - 3));
		}
		
		/**
		 * Getting class properties by filter
		 * 
		 * @param string $filter
		 * @return array
		 */
		private function getProperties($filter = 'PROTECTED') {
			$reflection = new ReflectionClass($this);
			
			switch($filter) {
				case 'PROTECTED':
					$reflectionFilter = ReflectionProperty::IS_PROTECTED;
					break;
				case 'STATIC':
					$reflectionFilter = ReflectionProperty::IS_STATIC;
					break;
				case 'PRIVATE':
					$reflectionFilter = ReflectionProperty::IS_PRIVATE;
					break;
				case 'PUBLIC':
				default:
					$reflectionFilter = ReflectionProperty::IS_PUBLIC;
					break;
			}
			
			$properties = $reflection->getProperties($reflectionFilter);
			
			if($properties) {
				$return_properties = array();
				foreach($properties as $property) {
					$return_properties[] = $property->getName();
				}
				
				return $return_properties;
			}
		}
		
		/**
		 * Getting result data
		 * 
		 * @return boolean|array
		 * @throws Exception 
		 */
		private function getRequestData() {
			if($this->url) {
				// loading XML
				$xml = simplexml_load_string(file_get_contents($this->url));
				if(sizeof($xml->channel->item) > 0) {
					$items = array();
					foreach($xml->channel->item as $item) {
						$items[] = $item;
					}
					return $items;
				} else {
					return false;
				}
			} else {
				throw new Exception('URL is not set');
			}
		}
		
		private function getRequestUrl() {
			
			$properties = $this->getProperties('PROTECTED');
			
			foreach(get_object_vars($this) as $key => $value) {
				if(in_array($key, $properties)) {
					if(isset($value) && (string)$value != '') {
						if(is_array($value)) {
							$value = array_shift($value);
							if(is_array($value)) {
								$value = implode(',', $value);
							}
						}
						
						$this->url .= sprintf('%s=%s&', $key, urlencode($value));
					}
				}
			}
			
			if(substr($this->url, -1) == '&') {
				$this->url = substr_replace($this->url, '', -1);
			}
			
			return $this->url;
		}
		
		/**
		 * setting url and returnig data
		 * 
		 * @return boolean|array 
		 */
		public function getResult() {
			$this->url = $this->getRequestUrl();
			return $this->getRequestData();
		}
		
	}
	
?>
