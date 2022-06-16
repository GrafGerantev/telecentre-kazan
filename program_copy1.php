#!/usr/bin/php

<?php

class ProgramCopy
{
    const MODE_DIR = 1;
    const MODE_FILE = 2;
    private $folderFrom = '';
    private $folderTo = '';
    private $doc_root = '/var/www/efir-test';

    function __construct($folderFrom, $folderTo)
    {
        $last_letter = $folderFrom[strlen($folderFrom) - 1];
        $this->folderFrom = ($last_letter == '\\' || $last_letter == '/') ? $folderFrom : $folderFrom . DIRECTORY_SEPARATOR;
        $last_letter = $folderTo[strlen($folderTo) - 1];
        $this->folderTo = ($last_letter == '\\' || $last_letter == '/') ? $folderTo : $folderTo . DIRECTORY_SEPARATOR;
    }

    private function _listenDir($mode, $subPath)
    {
        $arEntry = array();
        if (!$mode) $mode = self::MODE_FILE;
        if ($handleDir = opendir($this->doc_root . $subPath)) {
            while (false !== ($entry = readdir($handleDir))) {
                if ($entry == "." || $entry == "..") continue;
                if (!is_dir($this->doc_root . $subPath . $entry) && $mode == self::MODE_FILE)
                    $arEntry[] = $entry;
                if (is_dir($this->doc_root . $subPath . $entry) && $mode == self::MODE_DIR)
                    $arEntry[] = $entry;
            }
            closedir($handleDir);
        }
        return $arEntry;
    }

    private function _copyFile($fromPath, $toPath = null)
    {
        $data = '';
        if (!$toPath) $toPath = $fromPath;
        if (!copy($this->doc_root . $this->folderFrom . $fromPath, $this->doc_root . $this->folderTo . $toPath)) {
            $data = "ERROR: не удалось получить доступ файлу" . $this->doc_root . $this->folderTo . $toPath . "\r\n";
            //die();
        } elseif (!unlink($this->doc_root . $this->folderFrom . $fromPath)) {
            $data = "ERROR: не удалось удалить файл " . $this->folderFrom . $fromPath . "\r\n";
            //die();
        } else {
            $data .= "скопирован " . $fromPath . "\r\n";
        }
        file_put_contents($this->doc_root . '/log.txt', $data ,FILE_APPEND);
    }
    function start()
    {
        $arCity = $this->_listenDir(self::MODE_DIR, $this->folderFrom);
        foreach ($arCity as $city) {// цикл по папкам городов
            $arProgram = $this->_listenDir(self::MODE_DIR, $this->folderFrom . $city . DIRECTORY_SEPARATOR);
            foreach ($arProgram as $program) {// цикл по папкам программ
                //копируем файлы в папке программы
                $arFile = $this->_listenDir(self::MODE_FILE, $this->folderFrom . $city . DIRECTORY_SEPARATOR . $program . DIRECTORY_SEPARATOR);
                foreach ($arFile as $file)
                    $this->_copyFile($city . DIRECTORY_SEPARATOR . $program . DIRECTORY_SEPARATOR . $file);
                // ищем подпапки
                $arSubFolder = $this->_listenDir(self::MODE_DIR, $this->folderFrom . $city . DIRECTORY_SEPARATOR . $program . DIRECTORY_SEPARATOR);
                foreach ($arSubFolder as $subDir) {
                    $arFile = $this->_listenDir(self::MODE_FILE, $this->folderFrom . $city . DIRECTORY_SEPARATOR . $program . DIRECTORY_SEPARATOR . $subDir . DIRECTORY_SEPARATOR);
                    foreach ($arFile as $file)
                        $this->_copyFile($city . DIRECTORY_SEPARATOR . $program . DIRECTORY_SEPARATOR . $subDir . DIRECTORY_SEPARATOR . $file, $city . DIRECTORY_SEPARATOR . $program . DIRECTORY_SEPARATOR . $file);
                }
            }
        }
    }
}

$programCopy = new ProgramCopy('/upload/programs_temp/', '/upload/programs/');
$programCopy->start();


