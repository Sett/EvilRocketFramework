<?php
/**
 * Evil_Payment_Alfa - Расчеты через Альфа-Банк
 *
 * Created by JetBrains PhpStorm.
 * @author Alexander M Artamonov <art.alex.m@gmail.com>
 * @version 0.1
 * @date 27.06.11
 * @time 13:22
 */
 
class Evil_Payment_Alfa extends Evil_Payment_Tfb
{
    /**
     * @description payment form for user
     * @return Zend_Form|null
     */
    public function getForm()
    {
        $path = isset($this->_config['pathToFormConfig']) ?
                $this->_config['pathToFormConfig'] :
                __DIR__ . '/Alfa/application/configs/form.json';

        if(is_file($path))
            return new Zend_Form(json_decode(file_get_contents($path), true));

        return null;
    }

    /**
     * @description render view
     * @param Zend_Controller_Action $controller
     * @param string $personal
     * @return void
     */
    public function render(Zend_Controller_Action $controller, $personal = 'payment/alfa.phtml')
    {
        if(!is_file(APPLICATION_PATH . '/views/scripts/' . $personal))
        {
            $default = isset($this->_config['defaultViewName']) ? $this->_config['defaultViewName'] : 'index.phtml';

            $controller->getHelper('viewRenderer')->setNoRender(); // turn off native (personal) view
            $controller->view->addScriptPath(__DIR__ . '/alfa/application/views/scripts/');// add current folder to the view path
            $controller->view->form = $this->getForm();
            $controller->getHelper('viewRenderer')->renderScript($default);// render
        }
    }
}
