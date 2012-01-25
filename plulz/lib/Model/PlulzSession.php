<?php

/**
 * Auxiliar class to help manage the Session of the user
 *
 */
/*
    Use the static method getInstance to get the object.
*/
if(!class_exists('PlulzSession'))
{
	class PlulzSession
	{
		const SESSION_STARTED = TRUE;
		const SESSION_NOT_STARTED = FALSE;

		// The state of the _session
		private $_sessionState = self::SESSION_NOT_STARTED;

		// THE only instance of the class
		private static $_instance;

		private function __construct() {}

		/**
		*    Returns THE instance of 'Session'.
		*    The _session is automatically initialized if it wasn't.
		*
		*    @return    object
		**/

		public static function getInstance()
		{
			if ( !isset(self::$_instance))
			{
				self::$_instance = new self;
			}

			self::$_instance->startSession();

			return self::$_instance;
		}


		/**
		*    (Re)starts the _session.
		*
		*    @return    bool    TRUE if the _session has been initialized, else FALSE.
		**/

		public function startSession()
		{
			if ( $this->_sessionState == self::SESSION_NOT_STARTED )
			{
				$this->_sessionState = session_start();
			}

			return $this->_sessionState;
		}


		/**
		*    Stores datas in the _session.
		*    Example: $instance->foo = 'bar';
		*
		*    @param    name    Name of the datas.
		*    @param    value    Your datas.
		*    @return    void
		**/

		public function __set( $name , $value )
		{
			$_SESSION[$name] = $value;
		}


		/**
		*    Gets datas from the _session.
		*    Example: echo $instance->foo;
		*
		*    @param    name    Name of the datas to get.
		*    @return    mixed    Datas stored in _session.
		**/

		public function __get( $name )
		{
			if ( isset($_SESSION[$name]))
			{
				return $_SESSION[$name];
			}
		}

        public function set( $name, $value )
        {
            $_SESSION[$name] = $value;
        }

        public function get($name)
        {
            if ( isset($_SESSION[$name]))
			{
				return $_SESSION[$name];
			}
        }

		public function isDefined( $name )
		{
			return isset($_SESSION[$name]);
		}

		public function clear( $name )
		{
			unset( $_SESSION[$name] );
		}

		/**
		*    Destroys the current _session.
		*
		*    @return    bool    TRUE is _session has been deleted, else FALSE.
		**/
		public function destroy()
		{
			if ( $this->_sessionState == self::SESSION_STARTED )
			{
				$this->_sessionState = !session_destroy();
				unset( $_SESSION );

				return !$this->_sessionState;
			}

			return false;
		}
	}
}