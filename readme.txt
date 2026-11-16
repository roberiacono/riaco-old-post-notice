=== RIACO Old Post Notice ===
Contributors: riaco
Tags: old posts, notice, outdated content, last updated
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Shows a notice at the top of posts that have not been updated for a while.

== Description ==

RIACO Old Post Notice warns your readers when a post has not been updated for a given number of months, showing the last updated date.

This plugin was written as a learning example for the tutorial "Come sviluppare un plugin WordPress" (in Italian): https://robertoiacono.it/sviluppare-plugin-wordpress/

Features:

* Choose after how many months the notice appears.
* Customize the notice message.
* Automatic insertion at the top of old posts, or manual placement with the `[riaco_old_notice]` shortcode.
* Cleans up its option when uninstalled.

== Installation ==

1. Upload the `riaco-old-post-notice` folder to `/wp-content/plugins/`.
2. Activate the plugin through the Plugins screen.
3. Go to Settings > Old Post Notice to configure it.

== Frequently Asked Questions ==

= Is the notice shown on pages? =

No, only on single posts.

= How do I show the notice in a specific position? =

Disable "Automatic insertion" in the settings and add the `[riaco_old_notice]` shortcode where you want it.

== Changelog ==

= 1.0.0 =
* First release.
