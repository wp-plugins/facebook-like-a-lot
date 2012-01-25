<?php
/**
 * Special class for handling all messages that can be shown in the admin area or the front end area
 *
 * It uses session system for the messages, in order to avoid problems with the wordpress hook system
 * and when there are multiple users on the site
 * 
 */

if (!class_exists('PlulzNotices'))
{
    class PlulzNotices
    {
        /**
         * Session model/class that controls all system current sessions
         * @var object
         */
        protected $_PlulzSession;

        /**
         * Holds all current error notices
         * @var array
         */
        protected $_Errors;

        /**
         * Holds all current update notices
         * @var
         */
        protected $_Updates;


        public function __construct()
        {
            $this->_PlulzSession = PlulzSession::getInstance();
        }

        /**
         * Loads all current appended notices
         * @return void
         */
        public function getNotices()
        {
            if (empty($this->_Updates))
                $this->_Updates = $this->_PlulzSession->isDefined('updates')? $this->_PlulzSession->get('updates') : array();

            if (empty($this->_Errors))
                $this->_Errors  = $this->_PlulzSession->isDefined('errors') ? $this->_PlulzSession->get('errors') : array();
        }

        public function addError($name, $msg)
        {
            if ( empty($name) || empty($msg) )
                return false;

            // Checar se ja existe _session
            $this->_Errors[$name][] = $msg;

            $this->_PlulzSession->set('errors', $this->_Errors);
        }

        public function addUpdate($name, $msg)
        {
            if ( empty($name) || empty($msg) )
                return false;

            // Checar se ja existe _session
            $this->_Updates[$name][] = $msg;

            $this->_PlulzSession->set('updates', $this->_Updates);
        }

        public function getErrors()
        {
            return $this->_Errors;
        }

        public function getUpdates()
        {
            return $this->_Updates;
        }

        public function hasErrors()
        {
            $this->getNotices();

            if (!isset($this->_Errors) || empty($this->_Errors))
                return false;
            else
                return true;
        }

        public function hasUpdates()
        {
            $this->getNotices();

            if (!isset($this->_Updates) || empty($this->_Updates))
                return false;
            else
                return true;
        }

        public function clearUpdates()
        {
            $this->_Updates = array();
            $this->_PlulzSession->set('updates', $this->_Updates);
            $this->_PlulzSession->clear('updates');
        }

        public function clearErrors()
        {
            $this->_Errors = array();
            $this->_PlulzSession->set('errors', $this->_Errors);
            $this->_PlulzSession->clear('errors');
        }

        public function adminError()
        {
            if (!$this->hasErrors())
                return false;

            $output = '<div class="error">';

            foreach ($this->_Errors as $values)
            {
                foreach($values as $msg)
                    $output .= "<p>" . $msg . "</p>";
            }

            $output .= '</div>';

            $this->clearErrors();

            return $output;
        }

        public function frontError()
        {
            if (!$this->hasErrors())
                return false;

            $output = '<ul class="erros">';

            foreach ($this->_Errors as $values)
            {
                foreach($values as $msg)
                    $output .= "<li>" . $msg . "</li>";
            }

            $output .= '</ul>';

            $this->clearErrors();

            return $output;
        }

        public function adminUpdates()
        {
            if (!$this->hasUpdates())
                return false;

            $output = '<div class="updated">';

            foreach ($this->_Updates as $values)
            {
                foreach($values as $msg)
                    $output .= "<p>" . $msg . "</p>";
            }

            $output .= '</div>';

            $this->clearUpdates();

            return $output;
        }

        public function showAdminNotices()
        {
            $this->getNotices();

            echo $this->adminError();
            echo $this->adminUpdates();
        }

        public function showFrontNotices()
        {
            $this->getNotices();

            echo $this->frontError();
        }

    }   
}

?>