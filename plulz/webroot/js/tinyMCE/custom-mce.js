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
                    ed.execCommand( 'mceInsertContent', false, '[facebook_like_a_lot width="300" send="false" text="like" layout="standard" faces="false" ]');
                },
                image: url + "/like.png"
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