<?php
/**
 * This file is part of the project: DeeplTranslator.
 *
 * Copyright (c) 2022 Aleš Jandera <ales.jandera@gmail.com>
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace DeeplTranslator\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class Translate extends Command
{

    public $translatedArray = [];

    public $lang;

    protected function configure()
    {
        $this->setName('app:translator:translate');
        $this->setDescription('Translate inserted json to set language.');
        $this->addOption('file', null, InputOption::VALUE_REQUIRED);
        $this->addOption('lang', null, InputOption::VALUE_REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $file = $input->getOption('file');
            $this->lang = $input->getOption('lang');

            $string = file_get_contents($file);
            $json = json_decode($string, true);
            $this->readArray($json);
            $f = fopen($this->lang.'.json', 'w+');
            fwrite($f, json_encode($this->translatedArray));
            fclose($f);
            $output->writeln("New translate generate successfully");
        } catch (Exception $e) {
            $output->writeln($e->getMessage());
        }
    }

    private function readArray(array $data, $index = null) {
        foreach ($data as $key => $value) {
            if (!is_array($value)) {
                if ($index !== null) {
                    $this->translatedArray[$index][$key] = $this->translate($value, $this->lang);
                } else {
                    $this->translatedArray[$key] = $this->translate($value, $this->lang);
                }
            } else {
                if ($index !== null) {
                    $this->translatedArray[$index][$key] = [];
                } else {
                    $this->translatedArray[$key] = [];
                }
                $this->readArray($value, $key);
            }
        }
    }

    private function translate(string $text, string $lang) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,"https://api-free.deepl.com/v2/translate");
        $headers = [
            'Authorization: DeepL-Auth-Key '.getenv('DEEPL_KEY')
        ];

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
            http_build_query(['text' => html_entity_decode($text), 'target_lang' => $lang]));

        // Receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec($ch);

        curl_close ($ch);

        $res = json_decode($server_output);
        if (isset($res->translations[0]->text)) {
            return $res->translations[0]->text;
        }
        return "";
    }
}
