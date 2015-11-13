<?php

function __autoload($class) {
    $class = str_replace('app\\', '', $class);
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    $class = dirname(__FILE__) . DIRECTORY_SEPARATOR . $class . '.php';

    if(!file_exists($class)) {
        $directoryName = dirname($class);
        $fileArray = glob($directoryName . '/*', GLOB_NOSORT);
        $fileNameLowerCase = strtolower($class);

        $notFound = true;
        foreach($fileArray as $file) {
            if(strtolower($file) == $fileNameLowerCase) {
                $notFound = false;
                $class = $file;
            }
        }

        if($notFound) {
            return false;
        }
    }

    require_once($class);
}