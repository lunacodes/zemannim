=== Shabbat Zman Widget ===
Contributors: adatosystems
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=9BGXLVJUQW6DA
Tags: Shabbat, Shabbat Times, Zman
Requires at least: 3.3
Tested up to: 3.8
Stable tag: 1.8
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Displays candle lighting time, Torah reading, havdallah and other important aspects of Shabbat.

== Description ==
Displays Shabbat information for the coming week. Display is highly customizable, with options to display or hide:

* Hebrew date
* Candle lighting time
* Parsha HaShavuah/weekly Torah reading
* Sunrise and/or sunset
* Havdalah time
* Earliest time for Plag haMincha
* Latest time to say morning Shema

Within those options, there are choices to show English or Hebrew, Ashkenazi or Sephardi transliterations, and multiple options for calculating times (GR''A, M''A, etc)

This plugin makes two JSON calls to hebcal.com (one to get the Hebrew date and one for the weekly Torah reading). More information on how this feature works can be found here:
[http://www.hebcal.com/home/219/hebrew-date-converter-rest-api](http://www.hebcal.com/home/219/hebrew-date-converter-rest-api)
...and here...
[http://www.hebcal.com/home/195/jewish-calendar-rest-api](http://www.hebcal.com/home/195/jewish-calendar-rest-api)

This plugin also makes three JSON calls to GeoNames.org API to get the sunrise/sunset times for Friday and Saturday, and to obtain the latitude/longitude for the provided zip code. More information can be found here:
[http://www.geonames.org/export/web-services.html/](http://www.geonames.org/export/web-services.html/)


For Daily Zmanim display, please see this plugin: [http://wordpress.org/plugins/daily-zman-widget/](http://wordpress.org/plugins/daily-zman-widget)

== Installation ==
1. Download a copy of the zip file to your computer
1. Extract the files.
1. FTP to your webserver
1. Create a directory inside /wp-content/plugins named "shabbatzmanim"
1. Upload `zmanfriday.php` to the `/wp-content/plugins/shabbatzmanim` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Use Appareance -> Widgets to place this information on your page (does not currently support shortcodes or php inserts)

**Formatting**

Several CSS style codes are included: 

* #frizman: a DIV tag that wraps all the widget content
* #zmanbigtitle: a SPAN tag for the title of the widget
* #zmantitle: a SPAN tag for each of the titles within the widget.

Thus, you could format the text by adding the following to your style sheet:

`#frizman { font-size: 12px; }`
`#zmanbigtitle {`
`        font-size: 12px;`
`        font-weight: bold;`
`        text-decoration: underline;`
`       }`
`#zmantitle { font-weight: bold; }`

== Frequently asked questions ==

= Something isn't working. Any way I can see what might be going wrong? =
Check the "debug" option at the bottom of the widget to get some of the initial calculations that are coming in. Otherwise, contact the developer. I hear he's a great guy. 

= I put in my zipcode but I'm not getting the right location. =
Get the latitude/longitude for your location and use that instead.

= I put in the latitude/longitude but it's showing the completely wrong city =
The chances are when you googled "latitude longitude for xxxx" it gave you two regular numbers. What you need to pay attention to is the North/South and East/West items. If the number is South or West, then you need to make the number negative. Example: San Francisco is 37.7833 N, 122.4167 W. But if you leave the longitude as 122.4167 you'll get Weihai, China. 

== Screenshots ==

1. [Top half of admin panel](http://webdesign.adatosystems.com/files/2013/10/admin-1.jpg)
2. [Bottom half of admin panel](http://webdesign.adatosystems.com/files/2013/10/admin-2.jpg)
3. [View from the website](http://webdesign.adatosystems.com/files/2013/10/website-1.jpg)

== Changelog ==

= 1.8 =
* Changed https calls to http because it was causing errors on some systems that required certificates

= 1.7 =
* Fixed issues with Plag and Shema, proving yet again that developers should not QA their own code.

= 1.6 =
* Added options for Latitude/Longitude for those locations where the postal code doesn't work.
* If using Lat/Long, returns location name in that location's language (if possible)

= 1.5 =
* Using sunrise/sunset (instead of calculating it within the code) because of mis-matches in DST (daylight savings time) calculations.

= 1.4 =
* changed from using Google Maps API to GeoNames.org for lat/long and timezone data due to overload of Google's 2500 hit per day per IP, which was blocking sites hosted by NetworkSolutions and other larger providers.

= 1.3 =
* Changed URL calls to use a more secure cURL routine

= 1.2 =
* mostly repository updates, along with a few cosmetic changes.

= 1.1 =
* Added additional debugging for php version, plus an alternate method of determining Friday's date

= 1.0 =
* Initial Release


== Upgrade notice ==


== Arbitrary section 1 ==

