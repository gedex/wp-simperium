=== Simperium ===
Contributors:      akeda
Tags:              Simperium, log, logger, post-processing, event, data
Requires at least: 3.6
Tested up to:      3.9
Stable tag:        trunk
License:           GPLv2 or later
License URI:       http://www.gnu.org/licenses/gpl-2.0.html

This plugin contains easy-to-use API that uses WP HTTP API to send data to Simperium.

== Description ==

This plugin is intended for developer to send data to Simperium. Followings are use cases in which this plugin might come in handy:

1. You need post-processing (for instance pinging dozen web services after a post is published) without blocking request. In this case you call provided actions or helper methods, then you spawn long-live process (either from the same server or different server) that listens to changes from your Simperium bucket for post-processing.
1. You need to log events that happen during request lifecycle under restricted circumstances (for instance it won't possible to access log files on the server).

**How to use it**

Once this plugin is installed and activated, you can send data to Simperium in following ways:

1. Via action hook: `do_action( 'simperium_send_data', $bucket, $data )` or `do_action( 'simperium_send_buffered_data', $bucket, $data )`.
   The first hook, will send the `$data` immediately, while the later will send the data to buffer and will send all
   buffered data to Simperium at once after calling `do_action( 'simperium_flush_buffer', $bucket )`. If `$bucket` arg is provided, it only flushes buffered data that's targetted to `$bucket`.
2. Via helper method: `WP_Simperium::send_data( $bucket, $data )` or `WP_Simperium::send_buffered_data( $bucket, $data )`.
   To flush buffer, you call `WP_Simperium::flush_buffer( $bucket )`. Again, `$bucket` arg is optional.

The value of `$data` MUST BE in key-value array structure as nested structure is not supported by Simperium.

Before using the action hooks or helper methods, you need to supply Simperium app credentials via `simperium_config` filter, for example:

`
add_filter( 'simperium_config', function() {
	return array(
		'app_id'  => 'YOUR_APP_ID',
		'api_key' => 'YOUR_API_KEY',
	)
} );
`

In addition to `app_id` and `api_key` you can pass `username` and/or `access_token` to the array config. If `access_token` is omitted, the plugin will request `access_token` from Simperium with provided `username` (if exists) or `get_bloginfo( 'admin_email' )` and store the info in option. Subsequent calls will read `access_token` information from option, but can be bypassed by providing `access_token` in array config. It's preferred to supply your own `access_token` or `username` that hasn't
registered yet. Please keep in mind that token has 30 days life span. If you're using `access_token` that's automatically retrieved by the plugin, you don't need to worry as scheduled event will refresh the token per 29 days.

**Sender Examples**

I've created [sender examples plugin](https://github.com/gedex/wp-simperium-sender-examples) that you can use as a starting point, though it will run without any customization. Currently it has following features:

* Send post data once post status is transitioned to public.
* Send new comment.
* More will come later..

**Consumer Examples**

I'm working to provide listener apps, written in PHP (stay tuned!), for now you can check [Simperium examples](https://simperium.com/samples/) and their awesome libraries.

**Contributing**

* Development of this plugin is done on [GitHub](https://github.com/gedex/wp-simperium). Pull requests are always welcome.
* For **Sender** apps feedback, please check its [GitHub repo](https://github.com/gedex/wp-simperium-sender-examples).
* For **Consumer** apps feedback, please stay tuned!

== Installation ==

1. Upload **Simperium** plugin to your blog's `wp-content/plugins/` directory and activate.
1. Hook into `simperium_config` filter either as drop-in plugin or in your theme's `functions.php`

== FAQ ==

= Does the plugin provides API to retrieve entity from Simperium =

No. The main purpose of this plugin is to send data (from WordPress) only, the decision of this plugin is to not to expose API to get/update/delete data. Simperium has [client libraries](URL) that expose get/update/delete data, you better use that.

== Changelog ==

= 0.1.0 =
Initial release
