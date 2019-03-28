# Daily Zemannim Plugin

A widget that calculates and displays prayer times, dates of fasts and holidays, and the weekly torah portion, according to halakha (Jewish Law). This plugin is a rewrite and extension of the [Daily Zman Widget](https://wordpress.org/plugins/daily-zman-widget/) by [Adato Systems](http://www.adatosystems.com/)

The plugin works by pulling the user's location, either by the HTML5 Geolocation API or by inputting the user's IP (retrieved from browser headers) into the DB-IP - IP Geolocation API. From there it extracts the user's longitude and latitude, and feeds that into the Google Maps Geocoding API, to obtain the user's city, state, and country.

Once this is done, it checks for Daylight Saving Times, and then uses a Javascript time object, with a UTC offset to caclulate the UTC time. The UTC time is then fed into the SunCalc library, which returns the times for sunrise and sunset, and then performs the relevant calculations, in order to generate the halakhic times.

## Requirements
* [HTML5 Geolocation API](https://developer.mozilla.org/en-US/docs/Web/API/Geolocation_API)
* [Google Maps Geocoding API](https://developers.google.com/maps/documentation/geocoding/intro)
* [Google Maps Time Zone API](https://developers.google.com/maps/documentation/timezone/intro)
* [SunCalc Library](https://github.com/mourner/suncalc)
* [jQuery](https://jquery.com/)
* [ipapi](https://ipapi.co/)
<!-- * [DB-IP - IP Geolocation API](https://db-ip.com/api/) -->

## Changelog

### [1.3.4] - 2019-03-28
Consolidates and removes redundant functions. Improves code logic.

**Removed Functions:**
* `hebCalWeekday()` was consolidated into `hebCalShabbat`
* `generateDateStrings()`
* `displayShabbatTimes()` consolidated into displayTimes()

**Renamed Functions:**
* `generateDatesWithHebcal()` is now `generatePreDates()`
* `calculateTimes` is now `calculateSunTimes()`

**Modified Functions:**
* `timesHelper()` - moved the `displayTimes()` call to parent function
* calculateTimes() - deprecated the Shabbat-specific calculations (which are now handled via Hebcal API).

**Other:**
* `perashaHeb` now outputs `פרשה` instead of `פרשת`
* Cleaned up unnecessary comments and `console.log` statements
* Replaced additional double quotes surrounding strings, with single quotes


### [1.3.3] - 2019-03-27
* Rewrote the Hebcal parsing logic to be more intelligent
* New functions:
    * getShabbatDate()
    * getCandleTimes()
    * getHebDate()
    * getPerasha()
    * getHabdalaTimes()
* ashkiToSeph() - added holiday list, and updated parsing logic to reflect Hebcal's API.
* Renamed hecalHol to hebCalWeekday
* Added padding to outputted text
* Removed Deprecated getLatLongByAddr()
* Code Quality:
    * Replace double quotes surrounding strings, with single quotes
    * Use dot notation, instead of bracket for js arrays
    * Utilize obj.forEach for better parsing of js arrays.

### 1.3.2 - 2019-02-06
* Generating zemannim by IP + hebcal API working.

### [1.3.1] - 2019-02-04
* Add docblocks and clean up code.
* Deprecate getLatLongByAddr()
* Deprecate abbrRegion()
* Fix bug with getting user address via IP
    * Switched from IP-DB to [ipapi](https://ipapi.co/)

###[1.2.0]
* Combine Shabbath and Weekday times into single widget

###[1.1.0]
* N/A

## Future
* Increase wp-admin options for customization
* Merge redundant logic in the hebcal getter functions
* Display Holidays, in addition to Shabbath

### Known Issues
* getGeoDetails: Needs an additional `for loop` in order to avoid `var state` defaulting to null.
