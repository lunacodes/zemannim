
// Element Names to Display Data to
var z_date = document.getElementById("zemanim_date");
var z_city = document.getElementById("zemanim_city");
// var z_hebrew = document.getElementById("zemanim_hebrew");
var z_shema = document.getElementById("zemanim_shema");
var z_minha = document.getElementById("zemanim_minha");
var z_peleg = document.getElementById("zemanim_peleg");
var z_sunset = document.getElementById("zemanim_sunset");    
var x = document.getElementById("zemanim_container");
var zemanim = document.getElementById("zemanim_display");


function getLocation() {
    // Get User Position via the HTML5 Geolocation API
    // Needs a Promise Chain with a reject call to the IP-based method

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(processLocationData);
    }
    else {
        zemanim.innerHTML = "Please enable location services to display zemanim";
    }
}

function processLocationData(position) {
    // Pass in the Lat & Long data to Google Maps
    // Needs to pass on the city, state, neighborhood

    // Get the lat & long info from the passed in data
    var lat = position.coords.latitude;
    var long = position.coords.longitude;

    // plug the data into a Google Maps Instance
    var point = new google.maps.LatLng(lat, long);
                new google.maps.GeoCoder().geocode({'latLng': point}, function(res, status) {
        var response = res;

        if (res[1]) {
            for (var i=0 < res.length; i++) {
                if (res[i].types[0] === "locality") {
                    var city = res[i].address_components[0].short_name;
                }

                if (res[i].types[0] === "neighborhood") {
                    var neighborhood = res[i].address_components[0].long_name;
                }

                if (res[i].types[0] === "administrative_area_level_1") {
                    var state = res[i].address_components[0].short_name;
                }
            } //end for loop
        } //endif  

        cityStr = city + ", " + state + " - " + neighborhood;

        /* I suspect most of the below code is unnecessary */
        window.lat = lat;
        window.long = long;
        window.city = city;
        generateTimes(lat, long, cityStr);
    
        return latLong = [window.lat, window.long];
    }); //end Google Maps Instance
}

/* This should probably be rewritten, as the jQuery is hopefully unnecessary */
function ConfirmDocumentReady() {
    // Confirms the page is ready for Google Maps API calls to run
    jQuery(document).ready(function() {
        if(navigator.geolocation) {
            getLocation();
        } else {
            jQuery('#zemanim_display').html('Geolocation is not supported by this browser.');
        }
    });
}

/* I don't think this is actually being used?? */
function checkForDST() {
    // Utilizes SunCalc Library
    // Check if Daylight Savings Times adjustment is needed

    // Detemerine Which part of the year we're in
    /* Why is this using January and July though... 
        aren't those the wrong months?!*/
    Date.prototype.stdTimezoneOffest = function() {
        var jan = new Date(this.getFullYear(), 0, 1);
        var jul = new Date(this.getFullYear(), 6, 1);
        return Math.max(jan.getTimezoneOffset(), jul.getTimezoneOffset());
    }

    // Check if our Time Zone Offset matches DST or not 
    Date.prototype.isDstObserved = function() {
        return this.getTimezoneOffset() < this.stdTimezoneOffest();
    }

    // If we're in DST, then return True
    var today = new Date();
    if (today.isDstObserved()) {
        return true;
    }
} // end checkForDST()

var dstCheck = checkForDST();

function cleanTimeStr(x) {
    // To be called by generateTimeStrings()
    // Cleans Up some of the problematic Time Strings
    var newTimeStr = x.toString();
    newTimeStr = ("0" + x).slice(-2);
    return newTimeStr; 
}

function generateTimeStrings(timeObj) {
    // Generate the strings in proper ymdhm format
    // For halakhic time calculation purposes
    // calls cleanupTimeStrings for one or two of them
    var year = timeObj.getFullYear();
    var month = cleanTimeStr(timeObj.getMonth() + 1);
    var day = cleanTimeStr(timeObj.getDate());
    var hour = cleanTimeStr(timeObj.getHours());
    var min = cleanTimeStr(timeObj.getMinutes());

    var buildTimeStr = year + "-" + month + "-" + day + " " + hour + ":" + min;
    return buildTimeStr;
}

function generateDateString(timeObj) {
    // Generates the Date String to be displayed on the page
    // Converts Month from Number in Month Name

    var monthInt = timeObj.getMonth();
    var monthList = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    var month = monthList[monthInt];
    var day = cleanTimeStr(timeObj.getDate());
    var year = timeObj.getFullYear();

    var buildDateStr = '<span id="zemanin_date">' + "Times for " + month + " " + day + ", " + year + '</span>';
    return buildDateStr;
}

/* Can this be broken down into two functions? */
function generateTimes(lat, long, city) {
    // Generates Strings for Halakhic Times then
    // Passes them to displayTimes()
    // Utilizes SunCalc Library

    var cityStr = city;
    var times = SunCalc.getTimes(new Date(), lat, long);
    var sunriseObj = times.sunrise;
    var offSet = sunriseObj.getTimezoneOffset() / 60;
    var offSetSec = offSet * 3600;
    var dateObj = new Date();
    var dateStr = generateDateString(dateObj);
    var sunriseStr = generateTimeStrings(sunriseObj);
    var sunsetObj = times.sunset;
    var sunsetStr = generateTimeStrings(sunsetObj);

    var SunriseDateTimeInt = parseFloat((new Date(sunriseStr).getTime() / 1000) - offSetSec);
    var SunsetDateTimeInt = parseFloat((new Date(sunsetStr).getTime() / 1000) - offSetSec);

    var latestShemaStr = '<span id="zmantitle">Latest Shema: </span>' + calculateLatestShema(sunriseSec, sunsetSec, offSetSec);
    var earliestMinhaStr = '<span id="zmantitle">Earliest Minḥa: </span>' + calculateEarliestMinha(sunriseSec, sunsetSec, offSetSec);
    var pelegHaMinhaStr = '<span id="zmantitle">Peleḡ HaMinḥa: </span>' + calculatePelegHaMinha(sunriseSec, sunsetSec, offSetSec);
    var displaySunsetStr = '<span id="zmantitle">Sunset: </span>' + unixTimestampToDate(SunsetDateTimeInt+offSetSec);

    displayTimesOnPage(dateStr, cityStr, latestShemaStr, earliestMinhaStr, pelegHaMinhaStr, displaySunsetStr)
} // end generateTimes()

function unixTimestampToDate(timestamp) {
    // Helper function for all the generateTimes() and displayTimes()
    // Takes a UTC Number and turns it into human-readable date

    var date = new Date(timestamp * 1000);
    var hours = date.getHours();
    var ampm = "AM";
    var minutes = "0" + date.getMinutes();

    // Convert into 12hr format, if necessary
    if (hours) > 12) {
        hours -= 12;
        ampm = "PM";
    }
    else if (hours === 0) {
        hours = 12;
    }

    var formattedTime = hours + ':' + minutes.substr(-2);

    return formattedTime + " " + ampm;
} // end unixTimestampToDate

function calculateLatestShema(sunriseSec, sunsetSec, offSetSec) {
    // Calculates the time for the latest Shema

    var halakhicHour = Math.abs((sunsetSec - sunriseSec) / 12);
    var shemaInSeconds = sunriseSec + (halakhicHour * 3) + offSetSec;
    var latestShema = unixTimestampToDate(shemaInSeconds);

    return latestShema;
}

function calculateEarliestMinha(sunriseSec, sunsetSec, offSetSec) {
    // Calculates the time for the Earliest Minha

    var halakhicHour = (sunsetSec - sunriseSec) / 12;
    var minhaInSeconds = sunriseSec + (halakhicHour * 6.5) + offSetSec;
    var earliestMinha = unixTimestampToDate(minhaInSeconds);

    return earliestMinha;
}

function calculatePelegHaMinha(sunriseSec, sunsetSec, offSetSec) {
    // Calculates the time for the Peleg HaMinha

    var halakhicHour = (sunsetSec - sunriseSec) / 12;
    var minhaInSeconds = sunsetSec - (halakhicHour * 1.25) + offSetSec;
    var pelegHaMinha = unixTimestampToDate(minhaInSeconds);

    return pelegHaMinha;
}

function displayTimesOnPage(date, city, shema, minha, peleg, sunset) {
    // Displays the calculated times on the page

    z_date.innerHTML = date + "<br>";
    z_city.innerHTML = city + "<br>";
    // z_hebrew.innerHTML = hebrew + "<br>";
    z_shema.innerHTML = shema + "<br>";
    z_minha.innerHTML = minha + "<br>";
    z_peleg.innerHTML = peleg + "<br>";
    z_sunset.innerHTML = sunset + "<br>";

}


/* Promises */

/**
 * 1. Document Read -> Get Location Data
 * 2. Location Data Received -> Google Maps Processing
 */ 

let locationObtained = function() {

    return new Promise(function(resolve, reject) {

        if (receivedLocationInfo) {
            resolve('Location Obtained');
        }

        else {
            reject('Geolocation Not Supported');
        }
    });
}

locationObtained().then(processGoogleMapsData())