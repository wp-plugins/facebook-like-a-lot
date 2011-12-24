/**
 *
 * For each new custom button that is added you
 * should create a new ed.addButton with the functionality of the new
 * added button
 *
 */
(function() {
    tinymce.create('tinymce.plugins.teste', {
        init : function(ed, url) {
            ed.addButton('like', {
                title : 'Facebook Like Button',
                onclick : function() {
                    ed.execCommand( 'mceInsertContent', false, '[plulz_social_like width="350" send="false" font="arial" action="like" layout="standard" faces="false" ]');
                },
                image: url + "/like.png"
            });
            ed.addButton('comment', {
                title : 'Facebook Comment Box',
                onclick : function() {
                    ed.execCommand( 'mceInsertContent', false, '[plulz_social_comments width="450" colorscheme="light" num_posts="10"]');
                },
                image: url + "/comments.png"
            });
        },
        getInfo : function() {
            return {
                longname : "Facebook Shortcodes",
                author : 'Fabio Zaffani',
                authorurl : 'http://www.plulz.com/',
                infourl : 'http://www.plulz.com/',
                version : "1.0"
            };
        }
    });
    tinymce.PluginManager.add('like', tinymce.plugins.teste);
    tinymce.PluginManager.add('comment', tinymce.plugins.teste);
})();