=== Daily Zman Widget ===
Contributors: adatosystems
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=4ABF2RK76DKK4
Tags: Prayer Times, Davening times, davening, Zman, zmanim
Requires at least: 3.3
Tested up to: 3.7.1
Stable tag: 1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Displays Hebrew date, sunrise, sunset, and key times for prayers (latest Shema, earliest Plag, etc) along with multiple calculation options (GR''A, M''A).

== Description ==

Displays Hebrew date, sunrise, sunset, and key times for prayers (latest Shema, earliest Plag, etc) along with multiple calculation options (GR''A, M''A). Display is highly customizable, with options to display or hide:

* Hebrew date
* Sunrise and/or sunset
* Earliest time for Plag haMincha
* Latest time to say morning Shema

Within those options, there are choices to show English or Hebrew, Ashkenazi or Sephardi transliterations, and multiple options for calculating times (GR''A, M''A, etc)

This plugin makes one JSON call to hebcal.com for the the Hebrew date. More information on how this feature works can be found here:
[http://www.hebcal.com/home/219/hebrew-date-converter-rest-api](http://www.hebcal.com/home/219/hebrew-date-converter-rest-api)

This plugin also makes two JSON calls to Google's Maps API:  one to obtain the latitude/longitude for the provided zip code, and another to determine the time zone for that zip code.

More information can be found here:
[https://developers.google.com/maps/documentation/geocoding/](https://developers.google.com/maps/documentation/geocoding/)
...and here...
[https://developers.google.com/maps/documentation/timezone/](https://developers.google.com/maps/documentation/timezone/)

For Shabbat Zman display, please see this plugin: [http://wordpress.org/plugins/adatosystems-friday-zmanim/](http://wordpress.org/plugins/adatosystems-friday-zmanim/)

== Installation ==
1. Download a copy of the zip file to your computer
1. Extract the files.
1. FTP to your webserver
1. Create a directory inside /wp-content/plugins named "dailyzmanim"
1. Upload `zmanfriday.php` to the `/wp-content/plugins/dailyzmanim` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Use Appareance -> Widgets to place this information on your page (does not currently support shortcodes or php inserts)

**Formatting**
Several CSS style codes are included: 
* #dailyzman: a DIV tag that wraps all the widget content
* #zmanbigtitle: a SPAN tag for the title of the widget
* #zmantitle: a SPAN tag for each of the titles within the widget.

Thus, you could format the text by adding the following to your style sheet:

#dailyzman { font-size: 12px; }

#zmanbigtitle {
        font-size: 12px;
        font-weight: bold;
        text-decoration: underline;
}

#zmantitle { font-weight: bold; }

== Frequently asked questions ==

= The plugin works for part of the day, and then stops =

The Google API has a limit of 2,500 requests per day. If you need more than that, contact the developer for the pro version that covers high-volume websites.

= Something isn't working. Any way I can see what might be going wrong? =
Check the "debug" option at the bottom of the widget to get some of the initial calculations that are coming in. Otherwise, contact the developer. I hear he's a great guy. 

== Screenshots ==
1. [Top half of admin panel](http://webdesign.adatosystems.com/files/2013/10/screenshot-1.jpg)
2. [Bottom half of admin panel](http://webdesign.adatosystems.com/files/2013/10/screenshot-2.jpg)
3. [View from the website](http://webdesign.adatosystems.com/files/2013/10/screenshot-3.jpg)

== Changelog ==

= 1.0 =
* Initial Release

= 1.1 =
* Really just matched this to the way things are done in the Shabbat Zman widget
* Added options for Latitude/Longitude for those locations where the postal code doesn't work.
* If using Lat/Long, returns location name in that location's language (if possible)
* Using sunrise/sunset (instead of calculating it within the code) because of mis-matches in DST (daylight savings time) calculations.
* changed from using Google Maps API to GeoNames.org for lat/long and timezone data due to overload of Google's 2500 hit per day per IP, which was blocking sites hosted by NetworkSolutions and other larger providers.
* Changed URL calls to use a more secure cURL routine
* Added additional debugging for php version, plus an alternate method of determining Friday's date


== Upgrade notice ==



== Arbitrary section 1 ==

