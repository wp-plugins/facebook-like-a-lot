<?php

if (!class_exists('PlulzImport'))
{
    class PlulzImport
    {
        protected $_serverDir;

        public function __construct($_serverDir)
        {
            $this->_serverDir = $_serverDir;

            if (empty($this->_serverDir))
                return false;

            spl_autoload_register(array($this,'appModel'));
            spl_autoload_register(array($this,'libModel'));
            spl_autoload_register(array($this,'appController'));
            spl_autoload_register(array($this,'libController'));
            spl_autoload_register(array($this,'appHelper'));
            spl_autoload_register(array($this,'libHelper'));
        }

        public function appModel($className)
        {
            $file = "{$this->_serverDir}plulz/app/Model/$className.php";

            if (file_exists($file))
                include($file);
        }

        public function appController($className)
        {
            $file = "{$this->_serverDir}plulz/app/Controller/$className.php";

            if(file_exists($file))
                include($file);
        }

        public function appHelper($className)
        {
            $file = "{$this->_serverDir}plulz/app/View/Helper/$className.php";

            if(file_exists($file))
                include($file);
        }

        public function libController($className)
        {
            $file = "{$this->_serverDir}plulz/lib/Controller/$className.php";

            if(file_exists($file))
                include($file);
        }

        public function libModel($className)
        {
            $file = "{$this->_serverDir}plulz/lib/Model/$className.php";

            if(file_exists($file))
                include($file);
        }

        public function libHelper($className)
        {
            $file = "{$this->_serverDir}plulz/lib/View/Helper/$className.php";

            if(file_exists($file))
                include($file);
        }
    }
}