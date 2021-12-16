'use strict';

var zemannim = document.getElementById('zemannim_container');
var z_date = document.getElementById('zemannim_date');
var z_city = document.getElementById('zemannim_city');
var z_shema = document.getElementById('zemannim_shema');
var z_minha = document.getElementById('zemannim_minha');
var z_peleg = document.getElementById('zemannim_peleg');
var z_sunset = document.getElementById('zemannim_sunset');
var shabbat_zemannim = document.getElementById('shzm_container');
var sz_date = document.getElementById('shzm_date');
var sz_date_heb = document.getElementById('shzm_date_heb');
var sz_perasha = document.getElementById('shzm_perasha');
var sz_perasha_heb = document.getElementById('shzm_perasha_heb');
var sz_candles = document.getElementById('shzm_candles');
var sz_sunset = document.getElementById('shzm_sunset');
var sz_habdala = document.getElementById('shzm_habdala');

/**
 * Calculates the upcoming Shabbat's date, by determining the next Friday and Saturday
 * @param  {number} dayNum             The number day of the week
 * @param  {Date obj} dayDate       Today's date
 * @return {array.<Object>}         Array containing the start and endtime for Shabbat
 */
function getShabbatDate(dayNum, dayDate) {
  if (dayNum < 7) {
    var i = 7 - dayNum;
    var dateMod = dayDate + i;
    var fri = new Date();
    var sat = new Date();

    fri.setDate(dateMod - 2);
    sat.setDate(dateMod - 1);

    return [fri, sat];
  }
}

/**
 * Calculates candle lighting time
 * @param  {Object} data  Hebcal JSON data
 * @param  {Object} date  Date object with Friday's date
 * @return {string}        String representation of Friday's date.
 * Used for title, and for Sunset time calculations
 * (e.g. Shabbat Times for December 17, 2021)
 */
function getCandleTimes(data, date) {
  var candlesList = [];
  const d1 = new Date(date);

  data.forEach((item) => {
    var d2 = new Date(item.date);
    var same = d1.getDate() === d2.getDate();

    if ((item.category === 'candles') && same) {
      candlesList.push(item);
    }
  });
  return candlesList[0];
}

/**
 * Get Shabbat's date in Hebrew
 * @param  {Object} data  Hebcal JSON data
 * @param  {Object} date  Date object with Friday's date
 * @return {string}       Friday's Hebrew date
 */
function getHebDate(data, date) {
  var hebDatesList = [];
  const d1 = new Date(date);

  data.forEach((item) => {
    var d2 = new Date(item.date);
    var same = d1.getDate() === d2.getDate();

    if ((item.category === 'hebdate') && same) {
      hebDatesList.push(item);
      }
    });
  return hebDatesList[0].hebrew;
}
/**
 * Calculates the perasha's date in English
 * @param  {Object} data  Hebcal JSON info
 * @param  {Object} date  Saturday's date
 * @return {string}       Perasha's date in English
 */
function getPerasha(data, date) {
  var perashaList = [];
  const d1 = new Date(date);

  data.forEach((item) => {
    var d2 = new Date(item.date);
    var same = (d1.getDate() -1) === (d2.getDate());

    if ((item.category === 'parashat') && same ) {
      perashaList.push(item);
      return perashaList[0];
    }
  });
  return perashaList[0];
}

/**
 * Calculate the time for Habdala
 * @param  {Object} data Hebcal JSON Object
 * @param  {Object} date Saturday's Date
 * @return {Array}      Habdala date's info from Hebcal
 */
function getHabdalaTimes(data, date) {
  var habdalaTimes = [];
  const d1 = new Date(date);

  data.forEach((item) => {
    var d2 = new Date(item.date);
    var same = d1.getDate() === d2.getDate();

    if ((item.category === 'havdalah') && same) {
      habdalaTimes.push(item);
    }
  });
  return habdalaTimes;
}

/**
 * Calculate's Friday's sunset time
 * @param  {string} timestr  Date string for Friday's date
 * @return {string}          Friday's sunset time
 */
function hebCalGetSunset(timestr) {
    var str = timestr;
    var time = str.replace(/\D/g,'');
    var hr = time.slice(0,1);
    var min = time.slice(-2);
    min = parseInt(min) + 18;

    if (min >= 60) {
        min -=60;
        if (min < 10) {
            min = min.toString().padStart(2,0);
        }

        hr = parseInt(hr);
        hr += 1;
    }

    time = hr + ':' + min;
    return time;
}

/**
 * Helper function for hebCalShabbat to help determine whether today's the last day of the month
 * @param  {number} month  current month number
 * @param  {number} year   current year number
 * @return {Date}       number of days in the month
 *
 * WARNING 12/16/2021: the year param currently gets its data from the deprecated Date.getYear()
 * method in hebCalShab() function. Replace with Date.getFullYear()
 */
function getDaysInMonth(month, year) {
  return new Date(year, month, 0).getDate();
}

/**
 * Fetches Hebcal JSON data to use in time calculations, and generates strings
 * for dates and times. The input variables are generated
 * by getAddrDetailsByIp, or getGeoDetails and getTimeZoneID
 * @param  {string} cityStr  User's city, calculated either via GPS or IP
 * @param  {number} lat        User's latitude
 * @param  {int} long     User's longitude
 * @param  {str} tzid     User's timezone ID
 */
function hebCalShab(cityStr, lat, long, tzid) {
  const now = new Date();
  var month = now.getMonth() + 1;
  var year = now.getYear();
  var daysInMonth = getDaysInMonth(month, year);

  // If today's the last day of the month, advance to next month
  var tmpDayNum = now.getDate();
  if ( daysInMonth === tmpDayNum ) {
    month = month + 1;
  }

  var urlStr = 'https://www.hebcal.com/hebcal/?v=1&cfg=json&maj=on&min=on&nx=on&ss=on&mod=off&s=on&c=on&m=20&b=18&o=on&D=on&year=now&month=' + month + '&i=off&geo=pos' + '&latitude=' + lat + '&longitude=' + long + '&tzid=' + tzid;

  // Talk to Hebcal API
  fetch(urlStr)
    .then(function(response) {
      return response.json();
    })
    .then(function(res) {
      var data = res.items;

      // Store Hebcal times to use for calculations and formatting
      // Today's Data
      var today = now.toLocaleString('en-us', { month: 'long', day: 'numeric', year: 'numeric' });
      var todayStr = 'Times for ' + today;
      var todayNum = now.getDay();
      var todayDate = now.getDate();

			// Shabbath Section
      var shabbatDate = getShabbatDate(todayNum, todayDate);
      var fri = shabbatDate[0];
      var sat = shabbatDate[1];

      // Shabbath Strings
      var dateStr = fri.toLocaleString('en-us', { month: 'long', day: 'numeric', year: 'numeric' });
      var engdate = 'Shabbat Times for ' + dateStr;
      var hebdate = getHebDate(data, fri);

      // Candles & Sunset
      var candlesData = getCandleTimes(data, fri);
      var candles = candlesData.title;
      var sunset = hebCalGetSunset(candles);
      sunset = 'Sunset: ' + sunset;

      // Habdala Info
      var habdalaData = getHabdalaTimes(data, sat);
      var habdala = habdalaData[0].title;
      var index = habdala.indexOf('(') - 1;
      habdala = 'Haḇdala' + habdala.slice(index);

      // Perasha Info
      var perasha = getPerasha(data, sat);
      var perashaHeb = perasha.hebrew;
      var perashaEng = perasha.title;
      var phIndex = perashaHeb.indexOf('ת') + 1;
      perashaHeb = 'פרשה' + perashaHeb.slice(phIndex);

      // Transliterations
      var str = perashaEng;
      var firstSpace=str.indexOf(' ');
      var perashaShort= str.slice(firstSpace + 1);
      var a2s = ashkiToSeph(perashaShort, 'p');
      perashaEng = 'Perasha ' + a2s;

      var shabbatSet = [engdate, perashaEng, hebdate, perashaHeb, candles, sunset, habdala];
      var todaySet = timesHelper(lat, long);

      displayTimes(todaySet, shabbatSet, cityStr, todayStr);
  });
}

/**
 * Converts perasha and holiday names from Ashkenazi to Sepharadi transliteration
 * @param  {string} input  Name of perasha from Hebcal info
 * @param  {string} sel    Determines if we're parsing Perashiyoth or Holidays
 * @return {string}        Transliterated perasha or holiday name
 */
function ashkiToSeph(input, sel) {
  var perashaList = [
    ["Parashat", "Perasha"],
    ["Achrei Mot", "ʾAḥare Mot"],
    ["Balak", "Balaq"],
    ["Bamidbar", "Bemiḏbar"],
    ["Bechukotai", "Beḥuqqotay"],
    ["Beha'alotcha", "Behaʿalotekha"],
    ["Behar", "Behar"],
    ["Bereshit", "Bereshit"],
    ["Beshalach", "Beshallaḥ"],
    ["Bo", "Bo"],
    ["Chayei Sara", "Ḥayye Sara"],
    ["Chukat", "Ḥuqqat"],
    ["Devarim", "Deḇarim"],
    ["Eikev", "ʿEqeḇ"],
    ["Emor", "ʾEmor"],
    ["Ha'Azinu", "HaʾAzinu"],
    ["Kedoshim", "Qeḏoshim"],
    ["Ki Tavo", "Ki-Taḇo"],
    ["Ki Teitzei", "Ki-Teṣe"],
    ["Ki Tisa", "Ki Tisa"],
    ["Korach", "Qoraḥ"],
    ["Lech-Lecha", "Lekh-Lekha"],
    ["Masei", "Masʿe"],
    ["Matot", "Maṭṭot"],
    ["Metzora", "Meṣoraʿ"],
    ["Miketz", "Miqqeṣ"],
    ["Mishpatim", "Mishpaṭim"],
    ["Nasso", "Naso"],
    ["Nitzavim", "Niṣṣaḇim"],
    ["Noach", "Noaḥ"],
    ["Pekudei", "Pequḏe"],
    ["Pinchas", "Pineḥas"],
    ["Re'eh", "Reʾe"],
    ["Sh'lach", "Shelaḥ-Lekha"],
    ["Shemot", "Shemot"],
    ["Shmini", "Shemini"],
    ["Shoftim", "Shopheṭim"],
    ["Tazria", "Tazriaʿ"],
    ["Terumah", "Teruma"],
    ["Tetzaveh", "Teṣavve"],
    ["Toldot", "Toleḏot"],
    ["Tzav", "Ṣav"],
    ["Vaera", "VaʾEra"],
    ["Vaetchanan", "VaʾEtḥannan"],
    ["Vayakhel", "VayYaqhel"],
    ["Vayechi", "VaYeḥi"],
    ["Vayeilech", "VayYelekh"],
    ["Vayera", "VayYera"],
    ["Vayeshev", "VayYesheḇ"],
    ["Vayetzei", "VayYeṣe"],
    ["Vayigash", "VayYiggash"],
    ["Vayikra", "VayYiqra"],
    ["Vayishlach", "VayYishlaḥ"],
    ["Vezot Haberakhah", "VeZot HabBerakha"],
    ["Yitro", "Yitro"],
    ["Asara B'Tevet", "ʿAsara Beṭeḇet"]
  ];

  var holidayList = [
    ["Asara B'Tevet", "ʿAsara Beṭeḇet"],
    ["Candle lighting", "Haḏlaqat Nerot"],
    ["Chanukah", "Ḥanukka"],
    ["Chanukah: 1 Candle", "Ḥanukka: Ner I"],
    ["Chanukah: 2 Candles", "Ḥanukka: Ner II"],
    ["Chanukah: 3 Candles", "Ḥanukka: Ner III"],
    ["Chanukah: 4 Candles", "Ḥanukka: Ner IV"],
    ["Chanukah: 5 Candles", "Ḥanukka: Ner V"],
    ["Chanukah: 6 Candles", "Ḥanukka: Ner VI"],
    ["Chanukah: 7 Candles", "Ḥanukka: Ner VII"],
    ["Chanukah: 8 Candles", "Ḥanukka: Ner VIII"],
    ["Chanukah: 8th Day", "Ḥanukka: Yom VIII "],
    ["Days of the Omer", "ʿOmer"],
    ["Erev Pesach", "ʿEreḇ Pesaḥ"],
    ["Erev Purim", "ʿEreḇ Purim"],
    ["Erev Rosh Hashana", "ʿEreḇ Rosh Hashana"],
    ["Erev Shavuot", "ʿEreḇ Shaḇuʿot"],
    ["Erev Simchat Torah", "ʿEreḇ Simḥat Torah"],
    ["Erev Sukkot", "ʿEreḇ Sukot"],
    ["Erev Tish'a B'Av", "ʿEreḇ Tishʿa Beʾaḇ"],
    ["Erev Yom Kippur", "ʿEreḇ Yom HakKippurim"],
    ["Havdalah", "Haḇḏala"],
    ["Lag BaOmer", "Lag LaʿOmer"],
    ["Leil Selichot", "Seliḥot"],
    ["Pesach", "Pesaḥ"],
    ["Pesach I", "Pesaḥ Yom I"],
    ["Pesach II", "Pesaḥ Yom II"],
    ["Pesach II (CH''M)", "Pesaḥ (Ḥol HaMoʿḏ) Yom II"],
    ["Pesach III (CH''M)", "Pesaḥ (Ḥol HaMoʿḏ) Yom III"],
    ["Pesach IV (CH''M)", "Pesaḥ (Ḥol HaMoʿḏ) Yom IV"],
    ["Pesach Sheni", "Pesaḥ Sheni"],
    ["Pesach V (CH''M)", "Pesaḥ (Ḥol HaMoʿḏ) Yom V"],
    ["Pesach VI (CH''M)", "Pesaḥ (Ḥol HaMoʿḏ) Yom VI"],
    ["Pesach VII", "Pesaḥ Yom VII"],
    ["Pesach VIII", "Pesaḥ Yom VIII"],
    ["Purim", "Purim"],
    ["Purim Katan", "Purim Qaṭan"],
    ["Rosh Chodesh %s", "Rosh Ḥoḏesh"],
    ["Adar", "ʾAḏar"],
    ["Adar I", "ʾAḏar I"],
    ["Adar II", "ʾAḏar II"],
    ["Av", "ʾAḇ"],
    ["Cheshvan", "Marḥeshvan"],
    ["Elul", "ʾElul"],
    ["Iyyar", "ʾIyayr"],
    ["Kislev", "Kislev"],
    ["Nisan", "Nisan"],
    ["Sh'vat", "Sheḇaṭ"],
    ["Sivan", "Sivan"],
    ["Tamuz", "Tamuz"],
    ["Tevet", "Ṭeḇet"],
    ["Rosh Hashana", "Rosh Hashana"],
    ["Rosh Hashana I", "Rosh Hashana Yom I"],
    ["Rosh Hashana II", "Rosh Hashana Yom II"],
    ["Shabbat Chazon", "Shabbat Ḥazon"],
    ["Shabbat HaChodesh", "Shabbat HaḤoḏesh"],
    ["Shabbat HaGadol", "Shabbat Haggaḏol"],
    ["Shabbat Machar Chodesh", "Shabbat Maḥar Ḥoḏesh"],
    ["Shabbat Nachamu", "Shabbat Naḥamu"],
    ["Shabbat Parah", "Shabbat Para"],
    ["Shabbat Rosh Chodesh", "Shabbat Rosh Ḥoḏesh"],
    ["Shabbat Shekalim", "Shabbat Sheqalim"],
    ["Shabbat Shuva", "Shabbat Shuḇa"],
    ["Shabbat Zachor", "Shabbat Zakhor"],
    ["Shavuot", "Shaḇuʿot"],
    ["Shavuot I", "Shaḇuʿot Yom I"],
    ["Shavuot II", "Shaḇuʿot Yom II"],
    ["Shmini Atzeret", "Shemini ʿAṣeret"],
    ["Shushan Purim", "Shushan Purim"],
    ["Sigd", "Sigd"],
    ["Simchat Torah", "Simḥat Tora"],
    ["Sukkot", "Sukkot"],
    ["Sukkot I", "Sukkot Yom I"],
    ["Sukkot II", "Sukkot Yom II"],
    ["Sukkot II (CH''M)", "Sukkot (Ḥol HaMoʿḏ) Yom II"],
    ["Sukkot III (CH''M)", "Sukkot (Ḥol HaMoʿḏ) Yom III"],
    ["Sukkot IV (CH''M)", "Sukkot (Ḥol HaMoʿḏ) Yom IV"],
    ["Sukkot V (CH''M)", "Sukkot (Ḥol HaMoʿḏ) Yom V"],
    ["Sukkot VI (CH''M)", "Sukkot (Ḥol HaMoʿḏ) Yom VI"],
    ["Sukkot VII (Hoshana Raba)", "Sukkot (Hoshaʿna Rabba) Yom VII"],
    ["Ta'anit Bechorot", "Taʿanit Bekhorot"],
    ["Ta'anit Esther", "Taʿanit ʾEster"],
    ["Tish'a B'Av", "Tishʿa Beʾaḇ"],
    ["Tu B'Av", "Ṭu Beʾaḇ"],
    ["Tu BiShvat", "Ṭu Bishḇaṭ"],
    ["Tu B'Shvat", "Ṭu Bishḇaṭ"],
    ["Tzom Gedaliah", "Ṣom Geḏalya"],
    ["Tzom Tammuz", "Ṣom Tamuz"],
    ["Yom HaAtzma'ut", "Yom Haʿaṣmaʾut"],
    ["Yom HaShoah", "Yom Hashoʾa"],
    ["Yom HaZikaron", "Yom Hazzikkaron"],
    ["Yom Kippur", "Yom HakKippurim"],
    ["Yom Yerushalayim", "Yom Yerushalayim"],
    ["Yom HaAliyah", "Yom HaʿAliya"]
  ];

  input = input.replace(/\w\S*/g, function (txt) { return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase(); });

  var res = [];
  if (sel === 'p') {
    perashaList.forEach((item) => {
      var same = (input === item[0]);
      if (same) {
        res.push(item[1]);
      }
    });
  } else if (sel === 'h') {
    holidayList.forEach((item) => {
      var same = (input === item[0]);
      if (same) {
        res.push[item[1]];
      }
    });
  }

  var a2s = res;
  return a2s;
}

/**
 * Requests location permission from user for HTML5 Geolocation API,
 * and routes further calculations to use either Google Maps API or ipapi API.
 */
function getLocation() {
  var options = {
    enableHighAccuracy: true,
    maximumAge: 0
  };

  function error(err) {
    console.warn(`ERROR(${err.code}): ${err.message}`);
  zemannim.innerHtml = 'Please enable location services to display the most up-to-date Zemannim';
      getAddrDetailsByIp();
  }

  // If HTML 5 Geolocation permission enabled
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(getLatLngByGeo, error, options);
    }
}

/**
 * Separates [lat, long] from Geolocation data into lat & long vars
 * to pass to Google Maps API via getGeoDetails
 * @since  1.0.0
 * @param  {number|Array} position [lat, long] coordinate integers
 */
function getLatLngByGeo(position) {
  var pos = position;
  var lat = pos.coords.latitude;
  var long = pos.coords.longitude;

  getGeoDetails(lat, long);
}

/**
 * Parses JSON object from DB-IP API, and passes
 * lat, long, and city strings to hebCalShab()
 *
 * @since  1.3.1
 * @since  1.0.0
 */
function getAddrDetailsByIp() {
  // Get the user's ip & location info
  var urlStr = 'https://ipapi.co/json/';
  fetch(urlStr)
    .then(function(response) {
      return response.json();
    })
    .then(function(res) {
      var city = res.city;
      var state = res.region_code;
      var lat = res.latitude;
      var long = res.longitude;
      var tzid = res.timezone ;

      hebCalShab(city, lat, long, tzid);
    });
}


/**
 * Feeds lat & long coords into Google Maps API to obtain city and state info and
 * pass to generateTimes()
 *
 * @param  {float} lat_crd  User's lattitude coordinates
 * @param  {float} long_crd User's longitude coordinates
 * @return {string} cityStr User's City, State
 */
function getGeoDetails(lat_crd, long_crd) {
  var lat = lat_crd;
  var long = long_crd;
  var {cityStr, city, state} = '';

  var point = new google.maps.LatLng(lat, long);        new google.maps.Geocoder().geocode({'latLng': point}, function (res, status) {

    if (res[0]) {
      for (var i = 0; i < res.length; i++) {
        if (res[i].types[0] === 'locality') {
          city = res[i].address_components[0].short_name;
        }

        if (res[i].types[0] === 'administrative_area_level_1') {
          state = res[i].address_components[0].short_name;
        }
      }
    }

    if (null == state ) {
      cityStr = city;
    } else {
      cityStr = city + ', ' + state;
    }

    var rightNow = new Date();
    var utc = convToUTC(rightNow);

    getTimeZoneID(cityStr, lat, long, utc);
  });
}

/**
 * Helper function - Generates UTC string to pass to getTimeZoneID
 * @param  {Object} dateObj  Today's Date object
 * @return {string}          UTC string with user's local time, used in getTimeZoneID query
 */
function convToUTC(dateObj) {
  var time = dateObj;
  time = time.toISOString();
  time = Date.parse(time);
  time = time.toString();
  time = time.slice(0,10);

  return time;
}

/**
 * Determine the user's timezone via Google Time Zone API, and then passes all data to hebCalShab
 * @param  {string} city  User's city info - will be passed to Hebcal
 * @param  {number} lat   User lattitude coordinates
 * @param  {number} long  User longitude coordinates
 * @param  {string} utc   UTC string containing user's local time info
 */
function getTimeZoneID(city, lat,long, utc) {
  var tzKey = 'AIzaSyDgpmHtOYqSzG9JgJf98Isjno6YwVxCrEE';
  var tzUrlBase = 'https://maps.googleapis.com/maps/api/timezone/json?location=';
  var tzUrlStr = tzUrlBase + lat + ',' + long + '&timestamp=' + utc + '&key=' + tzKey;

  fetch(tzUrlStr)
    .then(function(response) {
      return response.json();
    })
    .then(function(res) {
      var tzid = res.timeZoneId;
      hebCalShab(city, lat, long, tzid);
    }
  );

}


/**
 * Removes leading 0 from 2-digit month or date numbers and returns to generateTimeStrings
 * @param  {number} x  Month number passed from generateTimeStrings()
 * @return {number}    Month number sans leading zero
 */
function formatTime(x) {
  var reformattedTime = x.toString();
  reformattedTime = ('0' + x).slice(-2);
  return reformattedTime;
}

/**
 * Helper function for timesHelper().
 * Splits timeObj array into year, month, day, hour, min, sec and returns a string with time info,
 * to be used in generating the final strings viewed by the user
 * @param  {number} timeObj Value passed in from generateTimes() after formatting from formatTime()
 * @return {string}   Time String in Y-M-D-H-M
 */
function generateSunStrings(timeObj) {
  var year = timeObj.getFullYear();
  var month = formatTime(timeObj.getMonth() + 1);
  var day = formatTime(timeObj.getDate());
  var hour = formatTime(timeObj.getHours());
  var min = formatTime(timeObj.getMinutes());
  var buildTimeStr = year + '-' + month + '-' + day + ' ' + hour + ':' + min;
  return buildTimeStr;
}

/**
 * Helper function to prepare final strings that the user will view
 * May deprecate in future, in favor of hebCalShab() or similar approach
 * @param  {number} lat   User's lattitude coordinates
 * @param  {number} long  User's longitude coordinates
 * @return {string}       Time String in Y-M-D-H-M
 */
function timesHelper(lat, long) {
  var todayTimesObj = SunCalc.getTimes(new Date(), lat, long);
  var todayTimes = calculateSunTimes(todayTimesObj, false);
  var todayStrSet = generateTimeStrings(todayTimes, false);

  return todayStrSet;
}


/**
 * Splits time object into year, month, day, hour, min, sec and returns buildTimeStr
 * @param  {number} timeObj Value passed from generateTimes() after formatting from formatTime()
 * @return {string}         Time String in Y-M-D-H-M
 */
function generateTimeStrings(timeSet, shabbat) {
  var sunrise = timeSet[0];
  var sunset = timeSet[1];
  var offSet = timeSet[2];
  var sunsetDateTimeInt = timeSet[3];

  var latestShemaStr = '<span id="zemannim_shema">Latest Shema: </span>' + calculateLatestShema(sunrise, sunset, offSet);
  var earliestMinhaStr = '<span id="zemannim_minha">Earliest Minḥa: </span>' + calculateEarliestMinha(sunrise, sunset, offSet);
  var pelegHaMinhaStr = '<span id="zemannim_peleg">Peleḡ HaMinḥa: </span>' + calculatePelegHaMinha(sunrise, sunset, offSet);
  var sunsetStr = '<span id="zemannim_sunset">Sunset: </span>' + unixTimestampToDate(sunsetDateTimeInt + offSet);

  if (shabbat) {
    var candleLighting = timeSet[4];
    var habdala = timeSet[5];
    var candleLightingStr = '<span id="zemannim_habdala">Candle Lighting (18 min): </span>' + unixTimestampToDate(candleLighting + offSet);
    var habdalaStr = '<span id="zemannim_habdala">Haḇdala (20 min): </span>' + unixTimestampToDate(habdala + offSet);
    var shabbatSet = [sunsetStr, candleLightingStr, habdalaStr];

    return shabbatSet;
  } else {
    var todaySet = [latestShemaStr, earliestMinhaStr, pelegHaMinhaStr, sunsetStr];

    return todaySet;
  }

}

/**
 * [calculateSunTimes description]
 * @param  {array} timeObj   Contains soon-to-be manupulated time data
 * @param  {boolean} shabbat Determines whether we are calculating
 * times for Shabbath or weekday
 * @return {array}           An array of time value integers
 */
function calculateSunTimes(timeObj, shabbat) {
  var times = timeObj;
  var sunriseObj = times.sunrise;
  var offSet = sunriseObj.getTimezoneOffset() / 60;
  var offSetSec = offSet * 3600;
  var sunriseStr = generateSunStrings(sunriseObj);
  var sunsetObj = times.sunset;
  var sunsetStr = generateSunStrings(sunsetObj);
  var SunriseDateTimeInt = parseFloat((new Date(sunriseStr).getTime() / 1000) - offSetSec);
  var sunsetDateTimeInt = parseFloat((new Date(sunsetStr).getTime() / 1000) - offSetSec);
  var sunriseSec = SunriseDateTimeInt - offSet;
  var sunsetSec = sunsetDateTimeInt - offSet;

  var timeSet = [sunriseSec, sunsetSec, offSetSec, sunsetDateTimeInt];
  return timeSet;
}

/**
 * [unixTimestampToDate description]
 * @param  {number} timestamp UTC Timestamp
 * @return {string}           Time in H:M:S
 * @since  1.0.0
 */
function unixTimestampToDate(timestamp) {
  var date = new Date(timestamp * 1000);
  var hours = date.getHours();
  var ampm = 'AM';
  var minutes = '0' + date.getMinutes();

  if (hours > 12) {
    hours -= 12;
    ampm = 'PM';
  }
  else if (hours === 0) {
    hours = 12;
  }
  var formattedTime = hours + ':' + minutes.substr(-2);
  return formattedTime + ' ' + ampm;
}

/**
 * Calculates the latest halakhic time to say the Shema Yisrael prayer
 * @param  {number} sunriseSec The time of sunrise, in seconds
 * @param  {number} sunsetSec  Time of sunset, in seconds
 * @param  {number} offSetSec  Offset for Time Zone and DST
 * @return {string}            Formatted string in H:M:S
 * @see unixTimestampToDate
 * @since  1.0.0
 */
function calculateLatestShema(sunriseSec, sunsetSec, offSetSec) {
  var halakhicHour = Math.abs((sunsetSec - sunriseSec) / 12);
  var shemaInSeconds = sunriseSec + (halakhicHour * 3) + offSetSec;
  var latestShema = unixTimestampToDate(shemaInSeconds);

  return latestShema;
}

/**
 * Calculates the earliest halakhic time to pray Minḥa
 * @param  {number} sunriseSec The time of sunrise, in seconds
 * @param  {number} sunsetSec  Time of sunset, in seconds
 * @param  {number} offSetSec  Offset for Time Zone and DST
 * @return {string}            Formatted string in H:M:S
 * @see unixTimestampToDate
 */
function calculateEarliestMinha(sunriseSec, sunsetSec, offSetSec) {
  var halakhicHour = (sunsetSec - sunriseSec) / 12;
  var minhaInSeconds = sunriseSec + (halakhicHour * 6.5) + offSetSec;
  var earliestMinha = unixTimestampToDate(minhaInSeconds);

  return earliestMinha;
}

/**
 * Calculates the latest halakhic time to pray Minḥa
 * @param  {number} sunriseSec The time of sunrise, in seconds
 * @param  {number} sunsetSec  Time of sunset, in seconds
 * @param  {number} offSetSec  Offset for Time Zone and DST
 * @return {string}            Formatted string in H:M:S
 * @see unixTimestampToDate
 */
function calculatePelegHaMinha(sunriseSec, sunsetSec, offSetSec) {
  var halakhicHour = (sunsetSec - sunriseSec) / 12;
  var minhaInSeconds = sunsetSec - (halakhicHour * 1.25) + offSetSec;
  var pelegHaMinha = unixTimestampToDate(minhaInSeconds);

  return pelegHaMinha;
}

/**
 * Receives time and location info from generateTimes() and
 * writes innerHtml for front-end display, via jQuery
 * @param  {string} date   Today's Date
 * @param  {string} city   User's City
 * @param  {string} shema  Lastest time to pray Shema
 * @param  {string} minha  Earliest time to pray Minḥa
 * @param  {string} peleg  Latest time to pray Minḥa
 * @param  {string} sunset Time of Sunset
 * @since  1.0.0
 */
function displayTimes(todaySet, shabbatSet, city, date) {
	// Weekday
  var shema = todaySet[0];
  var minha = todaySet[1];
  var peleg = todaySet[2];
  var sunset = todaySet[3];

  z_date.innerHTML = date + '<br>';
  z_city.innerHTML = city + '<br>';
  z_shema.innerHTML = shema + '<br>';
  z_minha.innerHTML = minha + '<br>';
  z_peleg.innerHTML = peleg + '<br>';
  z_sunset.innerHTML = sunset + '<br>';

	// Shabbath
  var sh_engdate = shabbatSet[0];
  var sh_perasha_en = shabbatSet[1];
  var sh_hebdate = shabbatSet[2];
  var sh_perasha_he = shabbatSet[3];
  var sh_candles = shabbatSet[4];
  var sh_sunset = shabbatSet[5];
  var sh_habdala = shabbatSet[6];

  sz_date.innerHTML = sh_engdate + '<br>';
  sz_perasha.innerHTML = sh_perasha_en + '<br>';
  sz_date_heb.innerHTML = sh_hebdate + '<br>';
  sz_perasha_heb.innerHTML = sh_perasha_he + '<br>';
  sz_candles.innerHTML = sh_candles + '<br>';
  sz_sunset.innerHTML = sh_sunset + '<br>';
  sz_habdala.innerHTML = sh_habdala + '<br>';
}

// Make sure we're ready to run our script!
jQuery(document).ready(function($) {
  getLocation();
});
