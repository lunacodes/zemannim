# Daily Zemannim

Daily Zemannim is a WordPress widget that I am currently developing for the [haSepharadi](https://hasepharadi.com) website, that displays daily zemmanim (halakhic times) according to Sepharadic tradition. 

## Libraries and APIs

The plugin makes use of the following libraries and APIs:
* [SunCalc](https://github.com/mourner/suncalc) by [Vladimir Agafonkin](https://github.com/mourner) to calculate sunrise and sunset times
* [DB-IP](https://db-ip.com/api/doc.php) to lookup location details, based on the user's IP Address
* [HTML5 Geolocation API](https://developer.mozilla.org/en-US/docs/Web/API/Geolocation_API) to obtain the user's latitude and longitude coordinates
* [Google Maps Geocoding API](https://developers.google.com/maps/documentation/geocoding/start) - to fetch the relevant location details, based on the user's lattitude and longitude coordinates for displaying zemmanim

## History

The original inspiration for Daily Zemmanim was [AdatoSystems' Daily Zman Widget](https://wordpress.org/plugins/daily-zman-widget/). The AdatoSystems plugin has the user input their zip code, in order to generate the times. We wanted to take that up a step by having the app automatically pull the user's location (either by the HTML5 Geolocation API or the user's IP Address), and dynamically generate the times without having to reload the page.

## Changelog

**1.01**
* Widget displays filler text for initial page load

## Issues

* `getGeoDetails` runs into issue with the not finding the state details, in the Google Maps API info. The function needs to recurse through remaining items in the array, in order to find this info. 
* Functions need docblocks

## Future Versions: 

* Refactor code to make better use of Promises
* Re-implement original options from the Daily Zman plugin, for customizing widget display


The project came about as an attempt to modify the original plugin

Currently the widget displays the following information:
* Date in M-D-Y format (ex August 03, 2018)
* City and State/Province
* Latest Shema
* Earliest Minḥa
* Peleḡ HaMinḥa
* Sunset

