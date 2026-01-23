=== Peanut Connect ===
Contributors: peanutgraphic
Tags: monitor, management, multisite, updates, health
Requires at least: 6.0
Tested up to: 6.4
Requires PHP: 8.0
Stable tag: 3.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Lightweight connector plugin for Peanut Monitor. Allows centralized site management from your manager site.

== Description ==

Peanut Connect is a lightweight plugin that connects your WordPress site to a Peanut Monitor dashboard. Once connected, your manager site can:

* Monitor site health (WordPress version, PHP version, SSL status)
* View available plugin and theme updates
* Remotely update plugins and themes (optional)
* Sync Peanut Suite analytics if installed (optional)

**Features:**

* **Minimal Footprint**: Lightweight plugin with no impact on site performance
* **Secure**: All communication is authenticated with unique site keys
* **Privacy Controls**: Choose what the manager can access
* **Peanut Suite Integration**: Automatically syncs analytics if Peanut Suite is installed

**How It Works:**

1. Install Peanut Connect on your site
2. Generate a site key from Settings > Peanut Connect
3. Add your site to Peanut Monitor using the site key
4. Manage your site remotely from the Monitor dashboard

== Installation ==

1. Upload the `peanut-connect` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > Peanut Connect
4. Generate your site key
5. Copy the key and add this site to your Peanut Monitor dashboard

== Frequently Asked Questions ==

= Is this plugin secure? =

Yes. All communication between your site and the manager requires authentication with a unique 64-character site key. You can regenerate the key at any time if compromised.

= Can I control what the manager can do? =

Yes. You can enable or disable specific permissions:
- Health checks (always allowed)
- View updates (always allowed)
- Perform updates (optional)
- Access analytics (optional)

= Does this work without Peanut Suite? =

Yes. Peanut Connect works on any WordPress site. If Peanut Suite is also installed, additional analytics data will be synced.

= Will this slow down my site? =

No. Peanut Connect only responds to authenticated API requests from your manager site. It adds no overhead to normal site operations.

== Changelog ==

= 3.0.9 =
* Fix toggle button labels for clarity (On/Off instead of confusing Enabled/Disabled)
* Toggle now shows green when security feature is active

= 3.0.8 =
* Fix CSS bleed affecting WordPress admin menu
* Scope Tailwind styles to prevent interference with WP admin

= 3.0.7 =
* Add Security Hardening section to Settings page
* Add Hub Permissions section to control what Hub can access
* Security features: Disable XML-RPC, Hide WP Version, Disable Comments, Custom Login URL
* Hub permissions: Allow/deny remote updates and analytics access
* Add Track Logged-In Users toggle

= 3.0.6 =
* Add Visitor Tracking toggle to Settings page
* Enable/disable pageview and visitor tracking from UI
* Tracking data syncs to Hub for Top Pages and Traffic Sources analytics

= 3.0.5 =
* Add Debug & Logging section to Settings page
* Display error counts and quick access to Error Log
* Toggle error logging on/off from Settings

= 3.0.4 =
* React SPA admin interface improvements
* Bug fixes and performance improvements

= 3.0.0 =
* Complete React SPA admin interface
* Dashboard with health summary, updates, and Hub status
* Health monitoring page with detailed checks
* Updates page with one-click update management
* Activity log for tracking site events
* Error log with filtering and export
* Settings page with Hub connection management
* Hub Mode feature (standard, hide Suite, disable Suite)

= 2.6.5 =
* Fix SSL detection in WP-CLI context
* End-to-end sync verification with Hub

= 2.6.0 =
* Hub Mode feature - control Peanut Suite behavior when connected
* Early filter registration for disable_suite mode

= 2.3.0 =
* NEW: Hub integration for centralized agency management
* NEW: Visitor tracking with cookie-based identification
* NEW: Event tracking (pageviews, scroll depth, form submissions)
* NEW: UTM parameter capture and attribution tracking
* NEW: Conversion tracking API
* NEW: Hub-managed popup system with multiple types (modal, slide-in, bar, toast, fullscreen)
* NEW: Automatic data sync to Peanut Hub (every 15 minutes)
* NEW: Frontend tracking JavaScript
* Database tables for local event queuing

= 1.0.0 =
* Initial release
* Site health monitoring
* Plugin/theme update management
* Peanut Suite analytics integration
* Permission controls

== Upgrade Notice ==

= 2.3.0 =
Major update: Connect to Peanut Hub for centralized agency management with visitor tracking, attribution, and hub-managed popups.

= 1.0.0 =
Initial release of Peanut Connect.
