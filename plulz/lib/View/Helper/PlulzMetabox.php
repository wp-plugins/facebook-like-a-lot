<?php

if (!class_exists('PlulzMetabox'))
{
    class PlulzMetabox
    {
        public function __construct(){}

        /**
         * Method that helps create metaboxes areas
         * @param string $width
         * @return void
         */
        public function createMetaboxArea( $width = '100%' )
        {
            echo    "<div class='postbox-container' style='width:{$width}'>";
            echo        "<div class='metabox-holder'>";
            echo            "<div class='meta-box-sortables ui-sortable'>";
        }

        /**
         * Close the metaboxarea
         * @return void
         */
        public function closeMetaboxArea()
        {
            echo            "</div>";
            echo        "</div>";
            echo    "</div>";
        }

        /**
         * Create any kinda of metabox in wordpress admin
         * @param string $title
         * @param null $extras
         * @internal param string $content
         * @return void
         */
        public function createMetabox( $title = 'Config', $extras = null)
        {
            if (isset($extras) && !empty($extras))
                extract($extras); // should have $id and $class

            isset($class) && !empty($class) ?   $class = " class='postbox {$class}'"  :   $class = ' class="postbox"';
            isset($id) && !empty($id)   ?   $id = " id={$id}"   :   $id = '';


            echo    "<div{$class}{$id}>" .
                        "<div class='handlediv' title='Click to Toggle'><br/></div>" .
                        "<h3 class='hndle'>{$title}</h3>" .
                        "<div class='inside'><table>";

        }

        public function closeMetabox()
        {
            echo "</table></div></div>";
        }
    }
}
?>