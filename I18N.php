<?php
 
    class Evil_I18N extends Zend_Controller_Plugin_Abstract
    {
        public function routeStartup(Zend_Controller_Request_Abstract $request)
        {
            $this->setLocale();
        }

        public function setLocale()
        {
            // putenv("LANG=ru_RU");

            $country = Evil_IP::geoIP('country');

            $config = Zend_Registry::get('config');
            // Задаем текущую локаль (кодировку)

            setlocale (LC_ALL, $config['locale'][$country]);
            // Указываем имя домена
            $domain = 'messages';

            // Задаем каталог домена, где содержатся переводы
            bindtextdomain ($domain, APPLICATION_PATH."/locale");

            // Выбираем домен для работы

            textdomain ($domain);

            // Если необходимо, принудительно указываем кодировку
            // (эта строка не обязательна, она нужна,
            // если вы хотите выводить текст в отличной
            // от текущей локали кодировке).
            bind_textdomain_codeset($domain, 'UTF-8');
        }
    }
