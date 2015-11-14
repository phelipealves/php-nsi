<?php

namespace NSI;

class NsiCallbackServer
{
    protected static $WEBSERVER_ROOT = '/web/';
    protected static $DEFAULT_INDEX_FILE = 'index.php.default';

    private $ipAddress;
    private $port;
    private $docRoot;
    private $isWindows;
    private $procId;
    private $listenerFilePath;

    public function __construct($ipAddress, $port, $listenerFilePath = null)
    {
        $this->ipAddress = $ipAddress;
        $this->port = $port;
        $this->docRoot = dirname(__FILE__).self::$WEBSERVER_ROOT;

        $osName = strtoupper(php_uname('s'));
        $this->isWindows = (strpos($osName, 'WINDOWS') !== false) ? true : false;
        $this->procId = null;

        if ($listenerFilePath == null) {
            $backTrace = debug_backtrace();
            foreach ($backTrace as $trace) {
                if (isset($trace['file'])) {
                    require_once $trace['file'];
                    if ($this->checkListenerCreateFunction()) {
                        $listenerFilePath = $trace['file'];
                        break;
                    }
                }
            }
        } else {
            $listenerFilePath = $this->normalizeListenerFilePath($listenerFilePath);
            require_once $listenerFilePath;
        }

        if (!$this->checkListenerCreateFunction()) {
            $message = sprintf('Could not load the __createCallbackListener() or the function does not return a '.
                "CallbackReceiver interface function in the listener file: '%s'",
                $listenerFilePath);
            throw new \Exception($message);
        }

        $this->listenerFilePath = $listenerFilePath;
    }

    public function start()
    {
        if ($this->isRunning()) {
            return false;
        }

        $this->createIndexFile();

        $startServerCommand = sprintf('%s > /dev/null &', $this->getStartCommand());
        proc_close(proc_open($startServerCommand, [], $exCode));

        try {
            $this->procId = $this->getProcId();
        } catch (\Exception $error) {
            $this->procId = null;
            throw new \Exception('The callback could not be started');
        }

        return true;
    }

    private function getStartCommand()
    {
        $windowsPhp = $this->isWindows ? '.exe' : '';
        $startServerCommand = sprintf('php%s -S %s:%s -t %s', $windowsPhp, $this->ipAddress, $this->port,
            $this->docRoot);

        return $startServerCommand;
    }

    private function createIndexFile()
    {
        $defaultIndexFilePath = $this->docRoot.self::$DEFAULT_INDEX_FILE;
        $defaultIndexFile = fopen($defaultIndexFilePath, 'r');
        if (!$defaultIndexFile) {
            throw new \Exception('Could not open default index file');
        }

        $defaultIndexContent = fread($defaultIndexFile, filesize($defaultIndexFilePath));
        fclose($defaultIndexFile);

        $defaultIndexContent = str_replace('<LOCAL_FILE>', $this->listenerFilePath, $defaultIndexContent);

        $indexFilePath = str_replace('.default', '', $defaultIndexFilePath);
        $indexFile = fopen($indexFilePath, 'w');
        if (!$indexFile) {
            throw new \Exception('Could not create index file');
        }

        fwrite($indexFile, $defaultIndexContent);
        fclose($indexFile);
    }

    private function getProcId()
    {
        $getProcCommand = sprintf('pgrep -f "%s"', $this->getStartCommand());
        $procId = shell_exec($getProcCommand);
        $procId = explode(PHP_EOL, $procId);
        $procId = (isset($procId[0]) && count($procId) > 1 && $procId[1] != '') ? $procId[0] : false;

        return $procId;
    }

    public function stop()
    {
        if (!$this->isRunning()) {
            return false;
        }

        $stopCommand = sprintf('kill -9 %s', $this->procId);
        exec($stopCommand, $return, $exitCode);

        $this->procId = false;
        if ($exitCode != 0) {
            throw new \Exception('The server is not running');
        }

        return true;
    }

    public function isRunning()
    {
        $this->procId = $this->getProcId();

        return $this->procId !== false;
    }

    private function checkListenerCreateFunction()
    {
        $definedFunctions = get_defined_functions()['user'];
        foreach ($definedFunctions as $function) {
            $functionName = explode('\\', $function);
            $functionName = $functionName[count($functionName) - 1];
            if (strtolower($functionName) == '__createcallbacklistener') {
                $callbackListener = call_user_func($function);
                if ($callbackListener instanceof CallbackReceiver) {
                    return true;
                }
            }
        }

        return false;
    }

    private function normalizeListenerFilePath($listenerFilePath)
    {
        $realPath = realpath($listenerFilePath);
        if ($realPath != null && $realPath !== false) {
            return $realPath;
        }

        $stack = debug_backtrace();
        $fileName = $stack[count($stack) - 1]['file'];
        $absolutePath = substr($fileName, 0, strrpos($fileName, strrchr($fileName, '/')));
        $absolutePath .= '/'.$listenerFilePath;

        $normalized = preg_replace('#\p{C}+|^\./#u', '', $absolutePath);
        $normalized = preg_replace('#/\.(?=/)|^\./|\./$#', '', $normalized);
        $regex = '#\/*[^/\.]+/\.\.#Uu';

        while (preg_match($regex, $normalized)) {
            $normalized = preg_replace($regex, '', $normalized);
        }

        if (preg_match('#/\.{2}|\.{2}/#', $normalized)) {
            throw new \LogicException('Path is outside of the defined root, path: ['.$absolutePath.'], '.
                'resolved: ['.$normalized.']');
        }

        return $normalized;
    }
}
