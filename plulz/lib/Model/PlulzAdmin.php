<?php
/**
 * This is a abstract class that when the plugin need to use all the admin integration functionality it must extends it
 * and declares all the variables / methods inside it
 *
 * The advantage is that everything else is easier since there are many pre built functions that help manage the Wordpress Admin
 * Panel
 *
 * CLASS OVERVIEW
 *
 * This class should be extended whenever we want to create a Theme or Plugin since it contains many helpfull
 * methods and variables in order to make an easy integration with the wordpress CMS
 *
 */
// Make sure there is no bizarre coincidence of someone creating a class with the exactly same name of this plugin
if ( !class_exists("PlulzAdmin") )
{
    class PlulzAdmin extends PlulzObjectAbstract
    {
        /**
         * Default curl options
         * @var array
         */
        public static $CURLOPT = array(
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_USERAGENT      => 'plulz-php-1.0'
        );

        /**
         * Default Plulz domain links
         * @var array
         */
        public static $DOMAIN = array(
            'www'   =>  'http://www.plulz.com',
            'feed'  =>  'http://www.plulz.com/feed',
            'api'   =>  'http://api.plulz.com'
        );

        public function __construct($name)
        {
            $this->_name = $name;
        }

        /**
         *
         * Get the latest news from Pazzani Tech blog
         * @return xml
         */
        public function fetchRSS()
        {
            $args = array(
                'feed'  =>  1
            );

            $results = $this->_requestAPI( $args );

            // Return the fetched XML converted to an SimpleXML object
            if ( $results['feed'] && !empty($results['feed']) )
                return simplexml_load_string($results['feed']);
            else
                return false;
        }

        /**
         *
         * Method that returns the newest releases plugins from Plulz
         * @param $args
         * @return array
         */
        public function fetchApi( $args )
        {
            if (empty($args))
                return false;

            $services = array(
                'feed'  =>  0,
                'api'   =>  1
            );

            if (is_array($args))
            {
                foreach ($args as $key => $value) // the params passed could be like 'help' => true or type => 'xml'..
                    $data[$key] = $value;
            }
            else
                $data = array($args => true);

            $results = $this->_requestAPI($services, $data);

            // Return the fetched XML converted to an object
            if ( $results['api'] && !empty($results['api']) )
                return simplexml_load_string($results['api']);
            else
                return false;

        }

        /*************************************************************************************
         *                             PROTECTED METHODS
         *************************************************************************************/

        /**
         * Method responsible for checking and connecting to the pazzani tech API
         * @param array $args
         * @param array|string $params
         * @return array
         */
        protected function _requestAPI( $args, $params = array() )
        {
            // First check if curl is enabled
            if ( !function_exists('curl_init') )
                throw new Exception('PlulzAPI needs the CURL PHP extension.');

            $default = array(
                'api'   =>  0,
                'feed'  =>  1
            );

            // overwrite the default values (if there is any new values)
            if ( is_array($args) )
                $services = array_merge( $default, $args );
            else
                $services = $default;

            if ($services['api'])
            {
                if ( !empty($params) )
                {
                    $api = curl_init();
                    $apiOpts = self::$CURLOPT;

                    $apiOpts[CURLOPT_URL] = self::$DOMAIN['api'];
                    $apiOpts[CURLOPT_POST] = 1;
                    $apiOpts[CURLOPT_POSTFIELDS] = http_build_query($params, null, '&');
                    $apiOpts[CURLOPT_HTTPHEADER] = array("Content-Type: application/x-www-form-urlencoded; charset=UTF-8");

                    curl_setopt_array($api, $apiOpts);

                    $apiResults = curl_exec($api);

                    curl_close($api);
                }
                else
                    $apiResults = 'You need to send some params to fetch from the API';
            }

            if ($services['feed'])
            {
                $feed = curl_init();
                $feedOpts = self::$CURLOPT;

                $feedOpts[CURLOPT_URL] = self::$DOMAIN['feed'];

                curl_setopt_array($feed, $feedOpts);

                $feedResults = curl_exec($feed);

                curl_close($feed);
            }

            return array(
                    'api'   =>  isset($apiResults) ? $apiResults : '',
                    'feed'  =>  isset($feedResults) ? $feedResults : ''
            );
        }

        public function getLoved()
        {
            $toFetch = array(
                'type'      =>  'xml',
                'loved'     =>  true,
                'plugin'    =>  $this->_name
            );

            $links = $this->fetchApi( $toFetch );

            $loved = $links->loved;

            $content = array();
            if (!$loved)    // Api is unreachable or slow
            {
                $content[]  =   array(
                    'link'  =>  'http://wordpress.org/extend/plugins',
                    'title' =>  'Give it a 5 star on Wordpress.org'
                );

                $content[]  =   array(
                    'link'  =>  'http://wordpress.org/extend/plugins',
                    'title' =>  'Link to it so others can easily find it'
                );
            }
            else
            {
                foreach( $loved->node as $element )
                {
                    $content[]  =   array(
                        'link'  =>  (string)$element->url,
                        'title' =>  (string)$element->title
                    );
                }
            }

            return $content;
        }

        public function getHelp()
        {
            $toFetch = array(
                'type'      =>  'xml',
                'help'      =>  true,
                'plugin'    =>  $this->_name
            );

            $links = $this->fetchApi( $toFetch );

            $helpLinks = $links->help;

            $content = array();
            if (!$helpLinks)    // Api is unreachable or slow
            {
                $content[] = array(
                    'link'  =>  'http://www.plulz.com',
                    'title' =>  'Plulz'
                );
            }
            else
            {
                foreach( $helpLinks->node as $element )
                {
                    $content[] = array(
                        'link'  =>  (string)$element->url,
                        'title' =>  (string)$element->title
                    );
                }
            }

            return $content;
        }

        public function getNews()
        {
            $news = $this->fetchRSS();

            // If somethings wrong with the feed, lets quietly leave this function...
            if (!$news)
                return;

            $content = array();

            // Atom or RSS ?
            for($i=0; $i<4; $i++)
            {
                if (isset($news->channel)) // RSS
                {
                    $content[] = array(
                        'url'   =>  $news->channel->item[$i]->link,
                        'title' =>  $news->channel->item[$i]->title,
                        'desc'  =>  $news->channel->item[$i]->description
                    );
                }
                else if (isset($news->entry)) // ATOM
                {
                    $content[] = array(
                        'url'   =>  $news->entry->link[$i]->attributes(),
                        'title' =>  $news->entry->title,
                        'desc'  =>  strip_tags($news->entry->content)
                    );
                }
            }

            return $content;
        }

        public function getDonate()
        {
            $toFetch = array(
                'type'      =>  'xml',
                'donate'    =>  true,
                'plugin'    =>  $this->_name
            );

            $link = $this->fetchApi( $toFetch );

            $donateLinks = $link->donate;

            if ( $donateLinks && is_string($donateLinks->description) )
                $content['desc'] = $donateLinks->description;
            else
                $content['desc'] = "I spend a lot of time making and improving this plugin, any donation would be very helpful for me, thank you very much :)";

            if ( $donateLinks && is_string($donateLinks->form) )
                $content['form'] = $donateLinks->form;
            else
                $content['form'] = '<form id="paypalform" action="https://www.paypal.com/cgi-bin/webscr" method="post"><input type="hidden" name="cmd" value="_s-xclick"><input type="hidden" name="hosted_button_id" value="NMR62HAEAHCRL"><input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!"><img alt="" border="0" src="https://www.paypalobjects.com/pt_BR/i/scr/pixel.gif" width="1" height="1"></form>';

            return $content;
        }
    }
}