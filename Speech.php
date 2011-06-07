<?php
/**
 * 
 * Класс для работы с голосом через гуглевское апи
 * @author nur
 *
 */
class Evil_Speech
{

    /**
     * урлы апишный серверов
     */
    protected static $textToSpeechUrl = 'http://translate.google.com/translate_tts';

    protected static $speechToTextUrl = 'https://www.google.com/speech-api/v1/recognize';

    /**
     * 
     * Преобразовать текст в mp3
     * @param string $text
     * @param false | string $toFile
     * @param string $lang
     * @example
     * $speech = Evil_Speech::textToSpeech('ебать колотить компьютер разговаривает');
     */
    public static function textToSpeech ($text, $toFile = false, $lang = 'ru')
    {
         //старый гуглокод, оставим здесь, вдруг снова откроют свой АПИ
    //    $client = new Zend_Http_Client(self::$textToSpeechUrl);
    //    $client->setParameterGet(array('ie' => 'UTF-8', 'q' => $text, 'tl' => $lang));
    //    $response = $client->request('GET');
    //    if ($toFile) {
    //        file_put_contents($toFile, $response->getBody());
    //        return true;
    //    } else {
    //        return $response->getBody();
    //    }

        $esf = new Evil_SpeechFestival();

        $tmp_file = '/tmp/'.time();

        if ($toFile)
        {
            $esf->textToSpeech($text, $tmp_file, $lang);
            return true;
        }
        else
        {
            $file =  $esf->textToSpeech($text, $tmp_file, $lang);
            header('Content-Type: audio/x-wav');
            header('Content-Disposition: attachment; filename="'. $file .'"');
            readfile($file);
        }
    }

    /**
     * Определение речи во входном файле
     * пока корректно определяется только mp3
     * @param string $input
     * @param string $lang
     */
    public static function speechToText ($input, $lang = 'ru-RU')
    {
        $file = self::_toFlac($input);
        $clinet = new Zend_Http_Client(self::$speechToTextUrl);
        $clinet->setParameterGet(array('xjerr' => '1', 'client' => 'chromium', 'lang' => $lang));
        $sampleRate = self::_getFileInfo($file, 'SampleRate');
        $clinet->setRawData(file_get_contents($file), 'audio/x-flac; rate=' . $sampleRate);
        $response = $clinet->request('POST');
        unlink($file);
        if ( 200 == $response->getStatus() )
        {
            return Zend_Json::decode($response->getBody());
        } else 
        {
            return false;
        }
    }

    /**
     * Получение информации о файле, через exiftool
     * Enter description here ...
     * @param string $path
     * @param string $tag
     */
    protected static function _getFileInfo ($path, $tag = null)
    {
        $cmd = 'exiftool -j ' . escapeshellarg($path);
        ob_start();
        system($cmd);
        $output = ob_get_clean();
        $params = Zend_Json::decode($output);
        return (null == $tag) ? $params[0] : $params[0][$tag];
    }
    
    /**
     * 
     * Конвертирование файла во flac формат
     * 
     * сконверченный flac файл будет лежать в той же папке что и исходный файл
     * пока нормально отрабатывает только с mp3 файлами
     * @param string $file
     * @param string $tmpName
     * @throws Exception
     */
    protected static function _toFlac($file)
    {


        /**
         *  --outfile            имя целевого файла
			--outdir             имя целевого каталог
         * Enter description here ...
         * @var unknown_type
         */
        $pathInfo = pathinfo($file);
        $pacl = sprintf('pacpl -v --overwrite --to flac --freq 44000 --channels 1 %s',$file);
        exec($pacl,$out,$retVar);
        //Zend_Debug::dump($retVar);
        //Zend_Debug::dump($pacl);
        //Zend_Debug::dump($out);
        return $pathInfo['dirname'] . DIRECTORY_SEPARATOR . $pathInfo['filename'] . '.flac';
        /**
         * TODO:проверка на ошибки конвертации $retVar
         */
        /*
        if(0 == $retVar)
        {
            Zend_Debug::dump($retVar);
            Zend_Debug::dump($out);
             echo $tmpName;
            return $tmpName;
        } else 
        {
            throw new Exception($out);
        }*/
    }
}