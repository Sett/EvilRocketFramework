<?php
/**
 * 
 * Класс для осуществления звонков по VOIP
 * 
 * В папке Call лежит файл linphonerc он является конфигурационным файлом
 * для звонилки
 * 
 * @depends linphone-noc
 * @depends perl
 * @depends lame
 * @author NuR
 * 
 *
 */
//TODO: NuR:бОльшая конфигурабельность кода
class Evil_Call implements Evil_TransportInterface
{

    /**
     * Звонок через VOIP
     * @param string $phone
     * @param string $file
     * @return bool
     */
    public static function Call ($phone, $messageToSay)
    {
        $tmpFile = tempnam('', __CLASS__ . '_');
        /**
         * перегоняем текст в mp3
         */
        if (Evil_Speech::textToSpeech($messageToSay, $tmpFile)) {
            /**
             * 
             * перегоняем mp3 в wav
             * @var string
             */
            $prepearedFile = self::_prepareFile($tmpFile);
            /**
             * если файл сконвертился успешно пытаемся позвонить
             */
            if (false !== $prepearedFile) {
                /**
                 * делаем звонок
                 */
                $folderName = pathinfo(__FILE__, PATHINFO_DIRNAME) . '/Call/';
                chdir($folderName);
                $linphoneCmd = sprintf('perl %slinphone.pl %s %s', $folderName, escapeshellarg($phone), $prepearedFile);
                /**
                 * системный вызов нашего скрипта для звонка
                 */
                ob_start();
                system($linphoneCmd, $status);
                $output = ob_get_clean();
                //echo $output;
                // Zend_Debug::dump($status);
                unlink($prepearedFile);
                if (0 == $status) {
                    return true;
                } else {
                    Evil_Log::log(__CLASS__ . ': ' . $output, Zend_log::CRIT);
                    return false;
                }
            } else {
                Evil_Log::log(__CLASS__ . ': не смогли сконвертировать mp3 в wav', Zend_log::CRIT);
                return false;
            }
        } else {
            Evil_Log::log(__CLASS__ . ': не смогли сконвертировать текст в звуковое сообщение', Zend_log::CRIT);
            return false;
        }
    }

    /**
     * 
     * Звонок для сообщения что осталось жить 7 дней
     * @author NuR
     * @param string $phone
     * @return bool
     */
    public static function sevenDays ($phone)
    {
        return self::Call($phone, 'Тебе осталось жить 7 дней');
    }

    /**
     * 
     * подготовка файла
     * перегонка из mp3 в wav например
     * @param string $file
     * @return string $file 
     */
    private static function _prepareFile ($file)
    {
        $lameCmd = 'lame --decode --mp3input ' . escapeshellarg($file);
        ob_start();
            system($lameCmd, $status);
        $output = ob_get_clean();
        
        unlink($file);
        if (0 == $status) {
            return $file . '.wav';
        }
        return false;
    }
    
    /**
     * отправка сообщения
     * @see Evil_TransportInterface::send()
     */
    public function send($to, $message)
    {
        return $this->Call($to, $message);
    }
    
    /**
     * (non-PHPdoc)
     * @see Evil_TransportInterface::init()
     */
    public function init(array $config)
    {
        
    }
    
    /**
     * 
     * валидация номера телефона
     * @param string $phoneNumber
     * @return bool
     */
    private function _validate($phoneNumber)
    {
        $pattern = '/(7|8)/';
        $vlidator = new Zend_Validate_Regex($pattern);
    }
}