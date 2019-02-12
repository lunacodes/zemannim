# Daily Zemannim Plugin

A widget that calculates and displays prayer times, dates of fasts and holidays, and the weekly torah portion, according to halakha (Jewish Law). This plugin is a rewrite and extension of the [Daily Zman Widget](https://wordpress.org/plugins/daily-zman-widget/) by [Adato Systems](http://www.adatosystems.com/)

The plugin works by pulling the user's location, either by the HTML5 Geolocation API or by inputting the user's IP (retrieved from browser headers) into the DB-IP - IP Geolocation API. From there it extracts the user's longitude and latitude, and feeds that into the Google Maps Geocoding API, to obtain the user's city, state, and country.

Once this is done, it checks for Daylight Saving Times, and then uses a Javascript time object, with a UTC offset to caclulate the UTC time. The UTC time is then fed into the SunCalc library, which returns the times for sunrise and sunset, and then performs the relevant calculations, in order to generate the halakhic times.

## Requirements
* [HTML5 Geolocation API](https://developer.mozilla.org/en-US/docs/Web/API/Geolocation_API)
* [Google Maps Geocoding API](https://developers.google.com/maps/documentation/geocoding/intro)
* [SunCalc Library](https://github.com/mourner/suncalc)
* [jQuery](https://jquery.com/)
* [ipapi](https://ipapi.co/)
<!-- * [DB-IP - IP Geolocation API](https://db-ip.com/api/) -->

## Changelog

### 1.3.2 - 2019-02-06
* Generating zemannim by IP + hebcal API working.

### [1.3.1] - 2019-02-04
* Add docblocks and clean up code.
* Deprecate getLatLongByAddr()
* Deprecate abbrRegion()
* Fix bug with getting user address via IP
    * Switched from IP-DB to [ipapi](https://ipapi.co/)

Version 1.2.0
* Combine Shabbath and Weekday times into single widget

Version 1.1.0
* N/A

## Future

### Zemannim
* Do I actually need to enqueue the Google Maps API? Or can I just run it as a promise instead??
* See if I can rewrite/get rid of some of what's in generateDatesWithHebCal()
* Need to rewrite the getAddrDetailsByGeo half of the code to feed into the new hebcal functionality.
    * Change all instances of Zemanim to Zemannim (make sure to check .scss files)
* Future Iteration: Leave SunCalc code in there, as a backup, in case Hebcal API fails
    * Probably take out for now though?
    * This seems possibly a bit overkill though? I like it though, b/c it's a bit more programmatically representation
    * Perhaps split these out into separate files or something?? Not sure how that would work
* Add in Back-End Options
* Need to convert Habdala


### General
* Write unit tests
* Rewrite as much code as possible into PHP.
    * Utilize the updated Hebcal SSL APIs, where relevant
* getGeoDetails: var state - immediately precedes if (state == null) - needs for loop, instead of just being set to null.
* Incorporate Promises more?
* getGeoDetails: Write an additional `for loop` in order to avoid `var state` defaulting to null.
<!-- * Incorporate Promises more? -->
* Add back-end/admin options for choosing transliteration style and which times to display back in
