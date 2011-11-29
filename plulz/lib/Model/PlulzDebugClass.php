<?php

if (!class_exists('PlulzDebug'))
{

    if ( !defined('SAVEQUERIES') && isset($_GET['debug']) && $_GET['debug'] == 'sql' )
        define('SAVEQUERIES', true);


    class PlulzDebug extends PlulzObjectAbstract
    {
        public function __construct()
        {
            $this->name = 'dubugger';
            $this->nonce = 'dubugger_nonce';
            
            $this->setAction('init', 'dump_phpinfo');
            $this->setAction('init', '', 10000000);
            $this->setAction('template_redirect', '', -10000000);
            $this->setAction('wp_footer', '', 10000000);
            $this->setAction('admin_footer', '', 10000000);
            $this->setAction('wp_print_scripts', 'init_dump');

            if (isset($_GET['debug']) && $_GET['debug'] == 'http' )
                $this->setAction('http_request_args', 'dump_http', 0, 2);


            $this->setAction( 'wp_footer', 'performance', 20);
        }
        /**
         * @param bool $visible
         * @return void
         */

        public function performance( $visible = false )
        {
            $stat = sprintf(  '%d queries in %.3f seconds, using %.2fMB memory',
                get_num_queries(),
                timer_stop( 0, 3 ),
                memory_get_peak_usage() / 1024 / 1024
                );

            echo $visible ? $stat : "<!-- {$stat} -->" ;
        }

        /**
         * dump()
         *
         * @param mixed $in
         * @return mixed $in
         **/

        public function dump($in = null)
        {
            echo '<pre style="margin-left: 0px; margin-right: 0px; padding: 10px; border: solid 1px black; background-color: ghostwhite; color: black; text-align: left;">';
            foreach ( func_get_args() as $var ) {
                echo "\n";
                if ( is_string($var) ) {
                    echo "$var\n";
                } else {
                    var_dump($var);
                }
            }
            echo '</pre>' . "\n";
            return $in;
        }

        /**
         * add_stop()
         *
         * @param mixed $in
         * @param string $where
         * @return mixed $in
         **/

        public function add_stop($in = null, $where = null)
        {
            global $sem_stops;
            global $wp_object_cache;
            $queries = get_num_queries();
            $milliseconds = timer_stop() * 1000;
            $out =  "$queries queries - {$milliseconds}ms";
            if ( function_exists('memory_get_usage') ) {
                $memory = number_format(memory_get_usage() / ( 1024 * 1024 ), 1);
                $out .= " - {$memory}MB";
            }
            $out .= " - $wp_object_cache->cache_hits cache hits / " . ( $wp_object_cache->cache_hits + $wp_object_cache->cache_misses );
            if ( $where ) {
                $sem_stops[$where] = $out;
            } else {
                dump($out);
            }
            return $in;
        } # add_stop()


        /**
         * dump_stops()
         *
         * @param mixed $in
         * @return mixed $in
         **/

        public function dump_stops($in = null)
        {
            if ( $_POST )
                return $in;
            global $sem_stops;
            global $wp_object_cache;
            $stops = '';
            foreach ( $sem_stops as $where => $stop )
                $stops .= "$where: $stop\n";
            dump("\n" . trim($stops) . "\n");
            if ( defined('SAVEQUERIES') && $_GET['debug'] == 'sql' ) {
                global $wpdb;
                foreach ( $wpdb->queries as $key => $data ) {
                    $query = rtrim($data[0]);
                    $duration = number_format($data[1] * 1000, 1) . 'ms';
                    $loc = trim($data[2]);
                    $loc = preg_replace("/(require|include)(_once)?,\s*/ix", '', $loc);
                    $loc = "\n" . preg_replace("/,\s*/", ",\n", $loc) . "\n";
                    dump($query, $duration, $loc);
                }
            }
            if ( $_GET['debug'] == 'cache' )
                dump($wp_object_cache->cache);
            if ( $_GET['debug'] == 'cron' ) {
                $crons = get_option('cron');
                foreach ( $crons as $time => $_crons ) {
                    if ( !is_array($_crons) )
                        continue;
                    foreach ( $_crons as $event => $_cron ) {
                        foreach ( $_cron as $details ) {
                            $date = date('Y-m-d H:m:i', $time);
                            $schedule = isset($details['schedule']) ? "({$details['schedule']})" : '';
                            if ( $details['args'] )
                                dump("$date: $event $schedule", $details['args']);
                            else
                                dump("$date: $event $schedule");
                        }
                    }
                }
            }
            return $in;
        } # dump_stops()

        /**
         * init_dump()
         *
         * @return void
         **/

        public function init_dump()
        {
            global $hook_suffix;
            if ( !is_admin() || empty($hook_suffix) ) {
                add_action('wp_footer', 'dump_stops', 10000000);
                add_action('admin_footer', 'dump_stops', 10000000);
            } else {
                add_action('wp_footer', 'dump_stops', 10000000);
                add_action("admin_footer-$hook_suffix", 'dump_stops', 10000000);
            }
        } # init_dump()

        /**
         * dump_phpinfo()
         *
         * @return void
         **/

        public function dump_phpinfo()
        {
            if ( isset($_GET['debug']) && $_GET['debug'] == 'phpinfo' ) {
                phpinfo();
                die;
            }
        }


        /**
         * dump_http()
         *
         * @param array $args
         * @param string $url
         * @return array $args
         **/

        public function dump_http($args, $url)
        {
            dump(preg_replace("|/[0-9a-f]{32}/?$|", '', $url));
            return $args;
        } # dump_http()


        /**
         * dump_trace()
         *
         * @return void
         **/

        public function dump_trace()
        {
            $backtrace = debug_backtrace();
            foreach ( $backtrace as $trace )
                dump(
                    'File/Line: ' . $trace['file'] . ', ' . $trace['line'],
                    'Function / Class: ' . $trace['function'] . ', ' . $trace['class']
                    );
        }

    }

    $PlulzDebug = new PlulzDebug();

}

?>