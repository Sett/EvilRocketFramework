<?php
/**
 * AmazonCDN
 * 
 * @desc Заменяет ссылки на котент, расположеный на текущем сервере, ссылками, расположеными на
 * удаленном сервере CDN.
 * 
 * Пути к файлам и папкам задаются в Application.ini
 * в виде
 * evil.amazonCDN.js.0.cdn_address = "http://d30tk8m4gt6k7.cloudfront.net/js/extjs/"
 * evil.amazonCDN.js.0.src_address = "/js/extjs/"
 * 
 * Где 
 * 	   evil.amazonCDN - необходимыйпреикс настроек.
 * 	   js - указывает тип файлов для замены (JS, CSS)
 *     0  - порядковый номер паттерна. Если необходимо указать несколько паттернов 
 *     		замены каждый новый паттерн должен иметь свой идентификатор.
 *     		идентифкатор может быть задан символьным именем
 *     src_address - тот адрес который необходимо заменить
 *     cdn_address - адрес на CDN сервере. Тоесть НА который нужно заменять
 *     	  * В качестве адресов можно указывать папки и файлы.
 *     		Если в src_address задан путь к файлу или папке, то в
 *     		cdn_address тоже должен быть указан путь к файлу или папке соотвественно
 *        * После названия директории рекомендуется ставить '/'
 *        	в ином случае паттерн  '/js/extjs' будет соотвествовать строке  '/js/extjsFinal'
 *          и будет заменен на "http://d30tk8m4gt6k7.cloudfront.net/js/extjsFinal".
 * 
 * @author Sergey
 * @date 21.06.2011
 * 
 */
	class Evil_AmazonCDN extends Zend_Controller_Plugin_Abstract
	{

		/**
		 * паттерн для поиска адресов JavaScript файлов
		 * @var string
		 */
		const JS_PREFIX = '<script type="text/javascript" src="';
		
		/**
		 * паттерн для поиска адресов CSS файлов
		 * @var string
		 */
		const CSS_PREFIX = '<link href="';
		
		/**
		 * содержат строки необходимые для замены
		 * Паралельные массивы. При добавлении в один необходимо
		 * добавить и в другой
		 */
		private $_address_inside = array();  //search
		private $_address_outside = array(); //destination
		
		/**
		 * application.ini
		 * @var array
		 */
		private $_config;
		
		/**
		 * @see Zend_Controller_Plugin_Abstract::dispatchLoopShutdown()
		 */
		public function dispatchLoopShutdown()
		{
			$this->_init();

			//проверяем адреса на корректность и правим
			$this->_validateAdress();
			
			/* Заполяем массивы $_address_inside и $_address_outside
			 * адресами путей, которые необходимо переписать
			 */ 				
			$this->_addPathToReplacmentArrays();
				
			// заменяем в Body документа полуичившимися паттернами
			$this->_replaceSrc();

		}
		
		/**
		 * Инициализация
		 * Считывает настрокйи application.ini
		 */
		public function _init()
		{
			//получаем ссылку на хранилище настроек
			try {
				$config = Zend_Registry::get('config');
			} catch (Exception $e) {
				throw new Exception('plugin AmazonCDN не смог получить доступ к настройкам в Application.ini', 500);
			}	
			
			$this->_config = $config['evil']['amazonCDN'];			
		}

		/**
		 * корректирует и проверяет адрес
		 */
		private function _validateAdress()
		{
			foreach ($this->_config as &$content_type){
				foreach ($content_type as &$content_number) {					
					//удаляет символ "*" в конце строки
					$content_number['src_address'] = rtrim($content_number['src_address'], '*');
				}
			}
		}

		/**
		 * Создание Паттернов путей для поиска и замены 
		 * Добавление их в address_inside и address_outside
		 */
		
		private function _addPathToReplacmentArrays()
		{	
			//добавляем JS адреса в массив для замены 
			if( isset($this->_config['js'])){		
				foreach ($this->_config['js'] as $numb){
					array_push($this->_address_inside,  self::JS_PREFIX . $numb['src_address']);
					array_push($this->_address_outside, self::JS_PREFIX . $numb['cdn_address']);				
				}
			}

			//добавляем CSS адреса в массив для замены 
			if( isset($this->_config['css'])){	
				foreach ($this->_config['css'] as $numb){
					array_push($this->_address_inside,  self::CSS_PREFIX . $numb['src_address']);
					array_push($this->_address_outside, self::CSS_PREFIX . $numb['cdn_address']);				
				}
			}
		}
		
		/**
		 * Парся HTML код, выдаваемый пользователю заменяем адреса наших js скриптов на амазонавские	
		 * @return void
		 */	
		private function _replaceSrc(){
			//количество элементов в массивах, содержащих адреса элементов для замены должно совпадать
			if(count($this->_address_inside) != 
				count($this->_address_outside)){
					throw new Exception("Plugin AmazonCdn. Количество  элементов в массивах адресов для замены не совпадает", 500);
				}
			
			$body = $this->getResponse()->getBody();
			$body = str_ireplace($this->_address_inside, 
								$this->_address_outside, 
								$body);
			$this->getResponse()->setBody($body);
										
		} 
		
		/**
		 * 
		 * Разбивает строку, содержащую исходные адреса
		 */
/*		private function _divideAddress(){
			//var_dump($this->_config['js']['src_address']);			
			foreach ($this->_config as &$src_types) {
				$src_address_array = explode(',', $src_types['src_address']); //массив из путей (адресов)
				
				//удаляем крайние пробелы во всех элементах массива адресов
				for ($i=0; $i<count($src_address_array); $i++){
					$src_address_array[$i] = trim($src_address_array[$i]);
				}				
				
				$src_types['src_address']=$src_address_array;
			}
			
			//var_dump($this->_config['js']['src_address']);		
		}*/
		
		
	}