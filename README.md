# Daily Zemannim Plugin

A widget that calculates and displays prayer times, dates of fasts and holidays, and the weekly torah portion, according to halakha (Jewish Law). This plugin started as a rewrite and extension of the [Daily Zman Widget](https://wordpress.org/plugins/daily-zman-widget/) by [Adato Systems](http://www.adatosystems.com/)

The plugin works by pulling the user's location, via 
[HTML5 Geolocation](https://developer.mozilla.org/en-US/docs/Web/API/Geolocation_API)
or [ipapi](https://ipapi.co/). 
From there it plugs the user's longitude and latitude into the 
[Google Maps Geocoding API](https://developers.google.com/maps/documentation/geocoding/intro) 
and generates the corresponding city, state, and country. It then 
calculates the current UTC time for the user's location, and 
plugs it into the [SunCalc Library](https://github.com/mourner/suncalc),
and performs sunrise and sunset-based calculations, in order to
generate the relevant halakhic times for 
daily prayer, Shabbat, and Habdala.

## Libraries & APIs

The following libraries and APIs are used in this plugin:

* [HTML5 Geolocation API](https://developer.mozilla.org/en-US/docs/Web/API/Geolocation_API)
* [Google Maps Geocoding API](https://developers.google.com/maps/documentation/geocoding/intro)
* [Google Maps Time Zone API](https://developers.google.com/maps/documentation/timezone/intro)
* [SunCalc Library](https://github.com/mourner/suncalc)
* [jQuery](https://jquery.com/)
* [ipapi](https://ipapi.co/)

## To-Do

* Increase wp-admin options for customization
* Write JS tests
* `ashkiToSeph`: DRY, Holiday functionality needs implementation
* Possible: Asbtract date checking from `getCandlesAndHebDate`, `getPerasha`, and `getHabdalaTimes`
* `formatTime` seems like it's doing the opposite of what it's supposed
* Possible: combine the two Minha calculation functions.
    * DRY is good, but current naming structure may be better
* Replace `jQuery(document).ready(function($)` with vanilla Javascript

## Changelog

### [1.4.2] - 2022-01-07
* Combined `getCandleTimes()` and `getHebDate()` into single `getCandlesAndHebDate()` function
* `ashkiToSeph` - fixed holiday conversion code
* `hebCalShab()` - replaced deprecated `Date.GetYear()` with `Date.GetFullYear()`
* Removed redundant variables and functions
* Replaced vars with const and let
* Cleaned up arrow functions
* Tidy up code alignment
* Replace `forEach` statements with `filter` statements where possible

### [1.4.1] - 2021-12-16
* Added Docblocks to all JS functions

### [1.4.0] - 2019-06-23
* Fix bug in `ashkiToSeph()` function, and other related parsing issues.

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

### [1.3.4] - 2019-03-28
* Removed Functions:
    * `hebCalWeekday()` consolidated into hebCalShabbat
    * `generateDateStrings()` was deprecated and unused
    * `displayShabbatTimes()` consolidated into displayTimes()

* Modified Functions:
    * `generateDatesWithHebcal()` renamed to `generatePreDates()`
    * Removed inner return statements (in forEach section) from getCandleTimes, getHebDate, getPerasha, and getHabdalaTimes et al.
    * `timesHelper()` - moved `displayTimes()` call to parent function
    * `calculateTimes()` - removed Shabbat calculations (since they're now done with hebCal)
    * `calculateTimes()` renamed to `calculateSunTimes()`

* Logic:
    * Improved code logic: moved call for `displayTimes()` from `timesHelper()` to `hebCalShab()`

* Front-End:
    * Corrected perashaHeb output (removed the tav from the end of perasha)

* Code Quality:
    * Removed unnecessary "Issues" section above the widget class
    * Removed some unnecessary `console.log`'s
    * Replaced double quotes with single quotes, wherever possible

* Need to:
    * DRY for candles, hebdate, habdala, perasha
    * Display holidays when a holiday is upcoming.

### [1.3.3] - 2019-03-27
* Rewrote the Hebcal parsing logic to be more intelligent
* New functions:
    * getShabbatDate()
    * getCandleTimes()
    * getHebDate()
    * getPerasha()
    * getHabdalaTimes()
* ashkiToSeph() - added holiday list, and updated parsing logic to reflect Hebcal's API.
* Renamed hebcalHol to hebCalWeekday
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

### [1.2.0]
* Combine Shabbath and Weekday times into single widget

### [1.1.0]
* N/A

