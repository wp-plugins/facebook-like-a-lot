<?php
/**
 *
 * Common methods that can be shared everywhere in wordpress system
 *
 *
 */

if (!class_exists('PlulzObjectAbstract'))
{
    abstract class PlulzObjectAbstract
    {
        /**
         * Store and show any error that might occurr to the user
         * @var array
         */
        public $PlulzNotices;               // Errors generated, normally outputed in the welcomeMessage method

        /**
         * For each configuration / options saved in db options or anywhere that is going to be
         * needed an admin page should be added in the $name var as a strreplaceing
         * @var array
         */
        protected $_name;                    // Name for each type of configuration to be saved

        protected $_className;

        public function __construct()
        {
            $this->_className = get_class($this);

            $this->PlulzNotices =  new PlulzNotices();

            try{
                $this->init();
            } catch (Exception $e) {
                echo 'Problems: ' . $e->getMessage() . "<br/>";
            }
        }

        /**
         * Method that normalizes and checks everything before we can use the class
         * @throws Exception
         * @return void
         */
        public function init()
        {
            if (empty($this->_name))
                throw new Exception( 'Name must be given to the class ' . $this->_className);
        }

        /**
         * Magic, redirects to the get() method
         * @param $key
         * @return mixed
         */
        public function __get($key)
        {
            return $this->get($key);
        }

        /**
         * Magic, redirects to the set() method
         * @param $key
         * @param $value
         */
        public function __set($key, $value)
        {
            $this->set($key, $value);
        }

        /**
         * Set any internal variable
         * @param $key
         * @param $value
         * @return void
         */
        public function set($key, $value)
        {
            $this->$key = $value;
        }

        /**
         * Get any internal variable
         * @param $key
         * @return mixed
         */
        public function get($key)
        {
            return $this->$key;
        }

        /**
         * Method that returns the name value for the current class
         * @return string $this->name
         */
        public function getName()
        {
            return $this->_name;
        }

        /**
         * Method that replace the default configurations
         *
         * @param array $defaultOptions
         * @param array $newOptions
         * @return array $output
         */
        protected function _replaceDefaults($defaultOptions, $newOptions)
        {
            foreach($newOptions as $name => $value)
            {
                $defaultOptions[$name] = $newOptions[$name];
	        }

            return $defaultOptions;
        }

    }
}