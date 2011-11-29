=== SEO Facebook Comment ===

Contributors: bemcapaz

Donate link: --//--

Tags: facebook like, like a lot, facebook social plugin, facebook like button, wordpress like, facebook like plugin, like plugin

Requires at least: 3.0

Tested up to: 3.2.1

Stable tag: 1.0


This plugin will insert a Facebook Like Button on Every blog post on your site. It also can be added through the shortcode [facebook_like_a_lot] anywhere you want.

== Description ==


<strong>What This Plugin Does?</strong>

This plugin embeds a Facebook Like Button on every post of your blog, you can choose if the plugin should appear in the beggning or end of the post.

It is also possible to embed the plugin through shortcodes.

== Screenshots ==

1. How it looks on the Theme to the user
2. The Plugin Installation View for the Admin
3. Plugin Admin Configuration View

== Installation ==


1. Upload `facebook-like-a-lot` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the Options of the plugin (App Id, App Secret and Admin E-mail are mandatory)
4. Profit :)


== Frequently Asked Questions ==

= How do I setup a Facebook App =
Everything regarding this question can be found here
[How to Create a Facebook App](http://www.plulz.com/how-to-create-a-facebook-app "How to Create a Facebook App")

= How do I find my Facebook User ID =
Everything regarding this question can be found here
[How to Create a Facebook App](http://www.plulz.com/how-to-get-my-facebook-user-id "How to get my Facebook User ID")

= It's normal that my comments appears only after some refreshes? =

Yes, in order to avoid excessive memory and server usage this plugins only updates the pages comments after someone loads that specific page.

So if someone update the page with a comment NOW and someone else opens that page the comment will not appear to that user yet. It will, however, appear normally on the Facebook comments box).

Loading a page will only make the Plugin sees that there is a new comment and that it should be added to the database, so in the next reload (2nd one) the comment will already appears at the page, if auto-approved or goes to the line of approval from the Admin.

= How SEO Facebook Comments keep track of the already added comments? =

The plugin uses a table that it creates (normally wp_comments_fbseo, depending on the prefix you used) to keep track of all the added comments and also the facebook users that added that comment.

= So what happens when I Remove this plugin and Reinstall it? =

On uninstall this plugin won't remove that table from database in order to avoid duplicating all the comments on a future re-install.

However you can access your database and manually remove the wp_comments_fbseo table, but keep in mind that it can cause you a lot of problems if you Reinstall this plugin later since it will duplicate all the Facebook Comments already added to your Wordpress Database.

== Changelog ==

= 1.0 =

* Initial Release


== Upgrade Notice ==

= 1.0 =

* No upgrades so far


-- 
Fabio Zaffani