=== Pro Like Button ===

Contributors: protport4991
Author: Andriy Prots
Tags: like button, voting, rating, vote, like, dislike, contest, rate, like counter, likes counter, post, posts, wordpress, comments,  page, pages, ratings, reviews, seo, vote, votes, plugin, voting button, wordpress vote post, wp like post, wp like plugin
Requires at least: 2.8
Tested up to: 6.6
Requires PHP: 5.6
Stable tag: 2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds buttons to posts with the ability to sort them.

== Description ==

If you’re looking for one of the best and fastest ways to add like and dislike functionality to your WordPress website, then the Pro Like Button plugin is for you! Pro Like Button is a free WordPress plugin that will allow users of your site to interact with posts that you liked and did not like, and you can track them on the site's control panel and sort posts according to these parameters.


= Features =

*   Possibility to choose in what page "Likes or Dislikes" will be displayed
*   Possibility to display buttons in any place via shortcode ['prolikebutton_shortcode']
*   Possibility to download your own icons for buttons
*   Possibility to sort posts using Likes and Dislikes
*  	10 button Templates (10 types of button layouts)
*   Custom Like & Dislike buttons texts
*   Available for logged and non-logged users
*   Support
*   Clean code (there is nothing redundant in the code)


= Translations =
Pro Like Button has been translated into the following languages:

*   English (United States)


== Installation ==

= From your WordPress dashboard =

1. Visit 'Plugins > Add New'
2. Search for 'Pro Like Button'
3. Activate 'Pro Like Button' from your Plugins page. (You will be greeted with a Welcome page.)

= From WordPress.org =

1. Download 'Pro Like Button'.
2. Upload the 'Pro Like Button' directory to your '/wp-content/plugins/' directory, using your favorite method (ftp, sftp, scp, etc...)
3. Activate 'Pro Like Button' from your Plugins page. (You will be greeted with a Welcome page.)


== Screenshot ==

1. This is the main plugin settings
2. These are ready-made button templates
3. Here you can download your own buttons icons
4. The buttons look like after installation


== Changelog ==

= 2.0 =
Add Statistics dashboard (likes/dislikes overview and sorting in wp-admin)
Add rate-limiting for the like/dislike AJAX handler to reduce spam and abuse
Add reCAPTCHA support for the like/dislike action
Add structured data (schema.org) output for like/dislike counts
Add WooCommerce product support
Redesign the plugin settings/admin pages
Fix several PHP 8+ warnings (activation, save form, posts_clauses)
Fix "Array to string conversion" notice in the shortcode handler
Unify text domain to `prolike-button` across the codebase

= 1.0.5 =
Security: fixed an unauthenticated SQL injection in the like/dislike AJAX handler (postid was concatenated into SQL; now validated as an integer and passed through $wpdb->prepare()). Reported by Enrico Marcolini, Claudio Marchesini and Dottor Marc.
Add "Where to display" option for Comments
Add "Who can like" setting (Anyone visiting / Members only)

= 1.0.3 =
Add new author url
Fix errors in the file readme.txt

= 1.0.2 =
Add output button shortcode

= 1.0 =
The initial version