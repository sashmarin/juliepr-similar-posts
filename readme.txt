=== Juliepr Similar Posts ===
Contributors: sashmarin
Tags: related posts, similar posts, categories
Requires at least: 6.5
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Shows three random posts by default from the current post's primary top-level category, or from a manually chosen list.

== Description ==

Juliepr Similar Posts renders a section titled “Articles on a similar topic” with three random published posts by default from the same top-level category. Each card displays the featured image when available, followed by the title.

The default heading is available in English and Russian.

When multiple categories are assigned, the plugin uses the deepest one. If several categories have the same depth, it uses the first one returned by WordPress. That category is then normalised to its top-level parent, so posts in sibling subcategories can be included.

The plugin adds no database tables or external dependencies. Its Settings page includes an optional cleanup checkbox, disabled by default, that removes the plugin's selected-posts post meta when the plugin is uninstalled.

At the bottom of the post editor, the “Similar posts” field lets you search published posts by title and add them to a list. When the list contains posts, the plugin chooses random published posts from it instead of the category. The current post is always excluded. The default number of results is three and can be changed with the block setting, shortcode attribute, or template-function argument. Remove every post from the list to restore category-based selection.

Developers can adjust the query with the `juliepr_similar_posts_query_args` filter, or replace the final HTML with the `juliepr_similar_posts_markup` filter. The latter receives the default markup, completed `WP_Query`, display arguments, current post ID, and selected top-level category.

== Installation ==

1. Upload the `juliepr-similar-posts` folder to `/wp-content/plugins/`.
2. Activate Juliepr Similar Posts in the Plugins screen.
3. Add the “Juliepr Similar Posts” block or `[juliepr_similar_posts]` to a post, or call `echo juliepr_similar_posts_render();` from a single-post template. Optionally set the number of posts and minimum publication year in the block settings, as `[juliepr_similar_posts number="6" min_year="2020"]`, or as `echo juliepr_similar_posts_render( 0, array( 'number' => 6, 'min_year' => 2020 ) );` in a template.

== Frequently Asked Questions ==

= Does it add related posts automatically? =

No. Place the shortcode in post content, or add the PHP function to the end of the template that renders posts.

= Can I use the Block Editor? =

Yes. Add the “Juliepr Similar Posts” block to a post. Its settings sidebar lets you set the number of posts and optionally set the minimum publication year for the selected posts.

= What happens when there are fewer matching posts than requested? =

The available matching posts are shown. If there are none, nothing is output.

== Changelog ==

= 1.0.0 =
* Initial release.
