=== Simperium ===
Contributors:      akeda
Tags:              Simperium, log, logger, post-processing, event, data
Requires at least: 3.6
Tested up to:      3.9
Stable tag:        trunk
License:           GPLv2 or later
License URI:       http://www.gnu.org/licenses/gpl-2.0.html

This plugin contains easy-to-use API that uses WP HTTP API to send data to Simperium. Simple tool is provided to listen changes from Simperium bucket.

== Description ==

This plugin is intended for developer to send data to Simperium. Followings are use cases in which this plugin might come in handy:

1. You need post-processing (for instance pinging dozen web services after a post is published) without blocking request. In this case you call provided actions or helper methods inside `action_name_for_when_post_is_published`. Another process will listen to changes from your Simperium bucket for post-processing.
1. You need to log events that happen during request lifecycle under restricted circumstances (for instance it won't possible to access log files on the server).

= How to use it =

Once this plugin is installed and activated, you can send data to Simperium in following ways:

1. Via action hook: `do_action( 'simperium_send_data', $data, $bucket )` or `do_action( 'simperium_send_buffered_data', $data, $bucket )`.
   The first hook, will send the `$data` immediately, while the later will send the data to buffer and will send all
   buffered data to Simperium at once by calling `do_action( 'simperium_flush_buffer', $bucket )`. If `$bucket` arg is provided, it only flush buffered data that's targetted to `$bucket`.
2. Via helper method: `WP_Simperium::send_data( $data, $bucket )` or `WP_Simperium::send_buffered_data( $data, $bucket )`.
   To flush buffered data, you need to call `WP_Simperium::flush_buffer( $bucket )`. Again, `$bucket` arg is optional.

Before using the action hooks or helper methods, you need to supply Simperium app credentials via `simperium_config` filter, for example:

`
add_filter( `simperium_config`, array(
	'app_id'  => 'YOUR_APP_ID',
	'api_key' => 'YOUR_API_KEY',
) );
`

In addition to `app_id` and `api_key` you can pass `username` and/or `access_token` to the array config. If `access_token` is omitted, the plugin will request `access_token` from Simperium with provided `username` (if exists) or `get_bloginfo( 'admin_email' )` and store the info in option. Subsequent calls will read `access_token` information from option, but can be bypassed by providing `access_token` in array config. It's preferred to supply your own `access_token` or `username` that hasn't
registered yet. Please keep in mind token has 30 days life span. If you're using access_token that's automatically retrieved by the plugin, you don't need to worry as there's scheduled event per 29 days to refresh the token.

= Tool =

This plugin comes with tool that resides under Tools. The page will show changes made to your Simperium app in real-time, should be helpful to check whether your data is getting sent or not.

= Sender Examples =

* [Send post data](URL) once post status is transitioned to public.
* [Send incoming comment](URL).
* [Send visitor data](URL) when hitting your page.
* [Send login attempt info](URL)

= Consumer Examples =

The possibility of consumer application is unlimited. Followings are some implementations that you can clone from [GitHub](URL):

* [Tweet](URL) once [post status is transitioned to public](URL).
* [Mirror post to Redis] once [post status is transitioned to public](URL).
* [Visitor in Google Map](URL) by consuming data from [visitor log](URL).

= Contributing =

* Development of this plugin is done on [GitHub](https://github.com/x-team/wp-simperium). Pull requests are always welcome.
* For **Sender** apps feedback, please check its [GitHub repo](URL)
* For **Consumer** apps feedback, please check its [GitHub repo](URL)

== Installation ==

1. Upload **Simperium** plugin to your blog's `wp-content/plugins/` directory and activate.

== FAQ ==

= Does the plugin provides API to retrieve entity from Simperium =

No. The main purpose of this plugin is to send data (from WordPress) only, the decision is always to not to expose API to get/update/delete data. Simperium has [client libraries](URL) that expose get/update/delete data, you better use that.

== Changelog ==

= 0.1.0 =
Initial release
