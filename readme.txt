=== Peanut Connect ===
Contributors: peanutgraphic
Tags: monitor, management, multisite, updates, health
Requires at least: 6.0
Tested up to: 6.4
Requires PHP: 8.0
Stable tag: 2.1.2
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

= 1.0.0 =
* Initial release
* Site health monitoring
* Plugin/theme update management
* Peanut Suite analytics integration
* Permission controls

== Upgrade Notice ==

= 1.0.0 =
Initial release of Peanut Connect.
