<?php

/**
 * Класс для работы с голосом через Festival
 * @author Adel Shigabutdinov
 *
 */

class Evil_SpeechFestival
{
   /**
     * Преобразовать текст в wav
     * @param string $text
     * @param string $toFile - указывать полный путь к файлу
     * @param string $lang
     * @example
     * $wavfile = Evil_SpeechFestival::textToSpeech('колотить компьютер разговаривает', /tmp/123123);
     * @result  
     * $wavfile = '/tmp/123123.wav'
      */

    public static function textToSpeech($text, $toFile, $lang = 'ru')
    {

        if ($lang == 'ru')
        {
            $param = "-eval '(voice_msu_ru_nsh_clunits)'";
        }
        else
        {
            $param = '';
        }

        exec('echo "' . $text . '">' . $toFile . '.txt');
        exec("text2wave " .$param . " " . $toFile . ".txt -o " . $toFile . ".wav");
        exec('rm ' . $toFile . '.txt');
        
        return $toFile . '.wav';
    }

}

//$engine = new Evil_SpeechFestival();
//file_put_contents('/tmp/i.txt', Evil_SpeechFestival::textToSpeech("Hello! I'm ubuntu Linux", '456456', 'en'));