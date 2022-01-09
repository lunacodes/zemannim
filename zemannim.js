'use strict';

/**
 * Requests location permission from user for HTML5 Geolocation API,
 * and routes further calculations to use either Google Maps API or ipapi API.
 */
function getLocation() {
  const options = {
    enableHighAccuracy: true,
    maximumAge: 0
  };

  function error() {
    getAddrDetailsByIp();
  }

  // If HTML 5 Geolocation permission enabled
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
    	getLatLngByGeo,
    	error,
    	options
  	);
  }
}

/**
 * Separates [lat, long] from Geolocation data into lat & long vars
 * to pass to Google Maps API via getGeoDetails
 * @since  1.0.0
 * @param  {number|Array} pos [lat, long] coordinate integers
 */
function getLatLngByGeo(pos) {
  const lat  = pos.coords.latitude;
  const long = pos.coords.longitude;

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
  const urlStr = 'https://ipapi.co/json/';
  fetch(urlStr)
    .then(function(response) {
      return response.json();
    })
    .then(function(res) {
      const state     = res.region_code;
      const city      = res.city;
      const city_str  = `${city}, ${state}`;
      const lat       = res.latitude;
      const long      = res.longitude;
      const tzid      = res.timezone ;

      hebCalShab(city_str, lat, long, tzid);
    });
}


/**
 * Feeds lat & long coords into Google Maps API to obtain city and state info and
 * pass to generateTimes()
 *
 * @param  {float} lat  User's lattitude coordinates
 * @param  {float} long User's longitude coordinates
 * @return {string} cityStr User's City, State
 */
function getGeoDetails(lat, long) {
  let city     = '';
  let cityStr  = '';
  let state    = '';

  const point = new google.maps.LatLng(lat, long);
  new google.maps.Geocoder().geocode({'latLng': point},
  	function (res, status) {

	    if (res[0]) {
	    	res.filter( data => {
	    		const type = data.types[0];
	    		const addr = data.address_components[0];

	    		if (type === 'locality') {
	    		  city = addr.short_name;
	    		}

	    		if (type === 'administrative_area_level_1') {
	    			state = addr.short_name;
	    		}
	    	});
	    }

	    cityStr = (null === state) ? city : city + ', ' + state;

	    const now = new Date();
	    const utc = convToUTC(now);

	    getTimeZoneID(cityStr, lat, long, utc);
	  });
}

/**
 * Helper function - Generates UTC string to pass to getTimeZoneID
 * @param  {Object} dateObj  Today's Date object
 * @return {string}          UTC string with user's local time, used in getTimeZoneID query
 */
function convToUTC(dateObj) {
  const dateISO = dateObj.toISOString();
  const time    = Date.parse(dateISO);

  return time.toString().slice(0, 10);
}

/**
 * Determine the user's timezone via Google Time Zone API, and then passes all data to hebCalShab
 * @param  {string} city  User's city info - will be passed to Hebcal
 * @param  {number} lat   User lattitude coordinates
 * @param  {number} long  User longitude coordinates
 * @param  {string} utc   UTC string containing user's local time info
 */
function getTimeZoneID(city, lat,long, utc) {
  const tzKey     = 'AIzaSyDgpmHtOYqSzG9JgJf98Isjno6YwVxCrEE';
  const tzUrlBase = 'https://maps.googleapis.com/maps/api/timezone/json?location=';
  const tzUrlStr  = tzUrlBase + lat + ',' + long + '&timestamp=' + utc + '&key=' + tzKey;

  fetch(tzUrlStr)
    .then(function(response) {
      return response.json();
    })
    .then(function(res) {
      const tzid = res.timeZoneId;
      hebCalShab(city, lat, long, tzid);
    }
  );
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
  const now         = new Date();
  let   month       = now.getMonth() + 1;
  const year        = now.getFullYear();
  const daysInMonth = getDaysInMonth(month, year);

  // If today's the last day of the month, advance to next month
  const day_num     = now.getDate();
  if ( daysInMonth === day_num ) {
    month = month + 1;
  }

  const urlStr = 'https://www.hebcal.com/hebcal/?v=1&cfg=json&maj=on&min=on&nx=on&ss=on&mod=off&s=on&c=on&m=20&b=18&o=on&D=on&year=now&month=' + month + '&i=off&geo=pos' + '&latitude=' + lat + '&longitude=' + long + '&tzid=' + tzid;

  // Talk to Hebcal API
  fetch(urlStr)
    .then( response => {
      return response.json();
    })
    .then( res => {
      const data = res.items;

      // Store Hebcal times to use for calculations and formatting
      // Today's Data
      const today             = now.toLocaleString('en-us', { month: 'long', day: 'numeric', year: 'numeric' });
      const todayStr          = 'Times for ' + today;
      const todayNum          = now.getDay();
      const todayDate         = now.getDate();

			// Shabbath Section
      const shabbatDate       = getShabbatDate(todayNum, todayDate);
      const fri               = shabbatDate[0];
      const sat               = shabbatDate[1];

      // Shabbath Strings
      const candlesAndHebDate = getCandlesAndHebDate(data, fri);
      const dateStr           = fri.toLocaleString('en-us', { month: 'long', day: 'numeric', year: 'numeric' });
      const engdate           = 'Shabbat Times for ' + dateStr;
      const hebdate           = candlesAndHebDate[0];

      // Candles & Sunset
      const candlesData       = candlesAndHebDate[1];
      const candles           = candlesData.title;
      let   sunset            = hebCalGetSunset(candles);
      sunset                  = 'Sunset: ' + sunset;

      // Habdala Info
      const habdalaData       = getHabdalaTimes(data, sat);
      let   habdala           = habdalaData[0].title;
      const index             = habdala.indexOf('(') - 1;
      habdala                 = 'Haḇdala' + habdala.slice(index);

      // Perasha Info
      const perasha           = getPerasha(data, sat);
      let   perashaHeb        = perasha.hebrew;
      let   perashaEng        = perasha.title;
      const phIndex           = perashaHeb.indexOf('ת') + 1;
      perashaHeb              = 'פרשה' + perashaHeb.slice(phIndex);

      // Transliterations
      const firstSpace        = perashaEng.indexOf(' ');
      const perashaShort      = perashaEng.slice(firstSpace + 1); // Just search by the first word?
      const a2s               = ashkiToSeph(perashaShort, 'p');
      perashaEng              = 'Perasha ' + a2s;

      const shabbatSet        = [engdate, perashaEng, hebdate, perashaHeb, candles, sunset, habdala];
      const todaySet          = timesHelper(lat, long);

      displayTimes(todaySet, shabbatSet, cityStr, todayStr);
  });
}

/**
 * Removes leading 0 from 2-digit month or date numbers and returns to generateTimeStrings
 * e.g.
 * @param  {number} x  Month number passed from generateTimeStrings()
 * @return {number}    Month number sans leading zero
 */
function formatTime(x) {
  let reformattedTime = x.toString();
  reformattedTime     = ('0' + x).slice(-2);

  return reformattedTime;
}

/**
 * [unixTimestampToDate description]
 * @param  {number} timestamp UTC Timestamp
 * @return {string}           Time in H:M:S
 * @since  1.0.0
 */
function unixTimestampToDate(timestamp) {
  const date    = new Date(timestamp * 1000);
  let hours     = date.getHours();
  let ampm      = 'AM';
  const minutes = '0' + date.getMinutes();

  if (hours > 12) {
    hours -= 12;
    ampm   = 'PM';
  }
  else if (hours === 0) {
    hours = 12;
  }
  const formattedTime = hours + ':' + minutes.substr(-2);
  return formattedTime + ' ' + ampm;
}

/**
 * Helper function to prepare final strings that the user will view
 * May deprecate in future, in favor of hebCalShab() or similar approach
 * @param  {number} lat   User's lattitude coordinates
 * @param  {number} long  User's longitude coordinates
 * @return {string}       Time String in Y-M-D-H-M
 */
function timesHelper(lat, long) {
  const todayTimesObj  = SunCalc.getTimes(new Date(), lat, long);
  const todayTimes     = calculateSunTimes(todayTimesObj);
  const todayStrSet    = generateTimeStrings(todayTimes, false);

  return todayStrSet;
}

/**
 * Helper function for hebCalShabbat to help determine whether today's the last day of the month
 * @param  {number} month  current month number
 * @param  {number} year   current year number
 * @return {Date}       number of days in the month
 */
function getDaysInMonth(month, year) {
  return new Date(year, month, 0).getDate();
}

/**
 * Calculates the upcoming Shabbat's date, by determining the next Friday and Saturday
 * @param  {number} dayNum             The number day of the week
 * @param  {Date obj} dayDate       Today's date
 * @return {array.<Object>}         Array containing the start and endtime for Shabbat
 */
function getShabbatDate(dayNum, dayDate) {
  if (dayNum < 7) {
    const adjust  = 7 - dayNum;
    const dateMod = dayDate + (adjust);
    const fri     = new Date();
    const sat     = new Date();

    fri.setDate(dateMod - 2);
    sat.setDate(dateMod - 1);

    return [fri, sat];
  }
}

/**
 * Generates candle lighting data and Hebrew Shabbat date
 * @param  {Object} data  Hebcal JSON data
 * @param  {Object} date  Date object with Friday's date
 * @return {array}        Hebrew date, Candle lighting data (array)
 * Candles data is used for title and sunset time calculations
 * (e.g. Shabbat Times for December 17, 2021)
 */
function getCandlesAndHebDate(data, date) {
	let candlesList  = [];
	let hebDatesList = [];
	const d1         = new Date(date);

	data.forEach((item) => {
	  const d2        = new Date(item.date);
	  const same_date = d1.getDate() === d2.getDate();

	  if ((item.category === 'hebdate') && same_date) {
	    hebDatesList.push(item);
	    }

	  if ((item.category === 'candles') && same_date) {
	    candlesList.push(item);
	  }
	});

	return [hebDatesList[0].hebrew, candlesList[0]];
}

/**
 * Calculate's Friday's sunset time
 * @param  {string} timestr  Date string for Friday's date
 * @return {string}          Friday's sunset time
 */
function hebCalGetSunset(timestr) {
  const time = timestr.replace(/\D/g,'');
  let hr     = time.slice(0,1);
  let min    = time.slice(-2);
  min        = parseInt(min) + 18;

  if (min >= 60) {
      min -= 60;
      if (min < 10) {
          min = min.toString().padStart(2,0);
      }

      hr  = parseInt(hr);
      hr += 1;
  }

  return hr + ':' + min;
}

/**
 * Calculate the time for Habdala
 * @param  {Object} data Hebcal JSON Object
 * @param  {Object} date Saturday's Date
 * @return {Array}      Habdala date's info from Hebcal
 */
function getHabdalaTimes(data, date) {
  let habdalaTimes = [];
  const d1         = new Date(date);

  data.forEach( item => {
    const d2       = new Date(item.date);
    const same     = d1.getDate() === d2.getDate();

    if ((item.category === 'havdalah') && same) {
      habdalaTimes.push(item);
    }
  });

  return habdalaTimes;
}

/**
 * Calculates the perasha's date in English
 * @param  {Object} data  Hebcal JSON info
 * @param  {Object} date  Saturday's date
 * @return {string}       Perasha's date in English
 */
function getPerasha(data, date) {
  let perashaList = [];
  const d1        = new Date(date);

  data.forEach( item => {
    const d2   = new Date(item.date);
    const same = (d1.getDate() -1) === (d2.getDate());

    if ((item.category === 'parashat') && same ) {
      perashaList.push(item);
      return perashaList[0];
    }
  });

  return perashaList[0];
}

/**
 * Converts perasha and holiday names from Ashkenazi to Sepharadi transliteration
 * @param  {string} input  Name of perasha from Hebcal info
 * @param  {string} sel    Determines if we're parsing Perashiyoth or Holidays
 * @return {string}        Transliterated perasha or holiday name
 */
function ashkiToSeph(input, sel) {
  const perashaList = [
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

  const holidayList = [
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

  const str = input.replace(/\w\S*/g, function (txt) { return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase(); });

  let a2s = [];
  if (sel === 'p') {
    perashaList.forEach( item => {
      if ( str === item[0] ) {a2s.push(item[1]); }
    });
  }

  if (sel === 'h') {
    holidayList.forEach( item => {
    	if ( str === item[0] ) {a2s.push(item[1]); }
    });
  }

  return a2s;
}

/**
 * [calculateSunTimes description]
 * @param  {array} timeObj   Contains soon-to-be manupulated time data
 * times for Shabbath or weekday
 * @return {array}           An array of time value integers
 */
function calculateSunTimes(timeObj) {
  const times              = timeObj;
  const sunriseObj         = times.sunrise;
  const offSet             = sunriseObj.getTimezoneOffset() / 60;
  const offSetSec          = offSet * 3600;
  const sunriseStr         = generateSunStrings(sunriseObj);
  const sunsetObj          = times.sunset;
  const sunsetStr          = generateSunStrings(sunsetObj);
  const SunriseDateTimeInt = parseFloat((new Date(sunriseStr).getTime() / 1000) - offSetSec);
  const sunsetDateTimeInt  = parseFloat((new Date(sunsetStr).getTime() / 1000) - offSetSec);
  const sunriseSec         = SunriseDateTimeInt - offSet;
  const sunsetSec          = sunsetDateTimeInt - offSet;

  return [sunriseSec, sunsetSec, offSetSec, sunsetDateTimeInt];
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
  const halakhicHour   = Math.abs((sunsetSec - sunriseSec) / 12);
  const shemaInSeconds = sunriseSec + (halakhicHour * 3) + offSetSec;

  return unixTimestampToDate(shemaInSeconds);
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
  const halakhicHour   = (sunsetSec - sunriseSec) / 12;
  const minhaInSeconds = sunriseSec + (halakhicHour * 6.5) + offSetSec;
  return unixTimestampToDate(minhaInSeconds);
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
  const halakhicHour   = (sunsetSec - sunriseSec) / 12;
  const minhaInSeconds = sunsetSec - (halakhicHour * 1.25) + offSetSec;
  return unixTimestampToDate(minhaInSeconds);
}

/**
 * Helper function for timesHelper().
 * Splits timeObj array into year, month, day, hour, min, sec and returns a string with time info,
 * to be used in generating the final strings viewed by the user
 * @param  {number} timeObj Value passed in from generateTimes() after formatting from formatTime()
 * @return {string}   Time String in Y-M-D-H-M
 */
function generateSunStrings(timeObj) {
  const year         = timeObj.getFullYear();
  const month        = formatTime(timeObj.getMonth() + 1);
  const day          = formatTime(timeObj.getDate());
  const hour         = formatTime(timeObj.getHours());
  const min          = formatTime(timeObj.getMinutes());

  return year + '-' + month + '-' + day + ' ' + hour + ':' + min;
}

/**
 * Splits time object into year, month, day, hour, min, sec and returns buildTimeStr
 * @param  {number} timeObj Value passed from generateTimes() after formatting from formatTime()
 * @return {string}         Time String in Y-M-D-H-M
 */
function generateTimeStrings(timeSet, shabbat) {
  const sunrise           = timeSet[0];
  const sunset            = timeSet[1];
  const offSet            = timeSet[2];
  const sunsetDateTimeInt = timeSet[3];

  const latestShemaStr    = '<span id="zemannim_shema">Latest Shema: </span>' + calculateLatestShema(sunrise, sunset, offSet);
  const earliestMinhaStr  = '<span id="zemannim_minha">Earliest Minḥa: </span>' + calculateEarliestMinha(sunrise, sunset, offSet);
  const pelegHaMinhaStr   = '<span id="zemannim_peleg">Peleḡ HaMinḥa: </span>' + calculatePelegHaMinha(sunrise, sunset, offSet);
  const sunsetStr         = '<span id="zemannim_sunset">Sunset: </span>' + unixTimestampToDate(sunsetDateTimeInt + offSet);

  if (shabbat) {
    const candleLighting  = timeSet[4];
    const habdala         = timeSet[5];
    const candleStr       = '<span id="zemannim_habdala">Candle Lighting (18 min): </span>' + unixTimestampToDate(candleLighting + offSet);
    const habdalaStr      = '<span id="zemannim_habdala">Haḇdala (20 min): </span>' + unixTimestampToDate(habdala + offSet);

    return [sunsetStr, candleStr, habdalaStr];
  } else {

    return [latestShemaStr, earliestMinhaStr, pelegHaMinhaStr, sunsetStr];
  }
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
	const zemannim       = document.getElementById('zemannim_container');
	const z_date         = document.getElementById('zemannim_date');
	const z_city         = document.getElementById('zemannim_city');
	const z_shema        = document.getElementById('zemannim_shema');
	const z_minha        = document.getElementById('zemannim_minha');
	const z_peleg        = document.getElementById('zemannim_peleg');
	const z_sunset       = document.getElementById('zemannim_sunset');
	const sz_date        = document.getElementById('shzm_date');
	const sz_date_heb    = document.getElementById('shzm_date_heb');
	const sz_perasha     = document.getElementById('shzm_perasha');
	const sz_perasha_heb = document.getElementById('shzm_perasha_heb');
	const sz_candles     = document.getElementById('shzm_candles');
	const sz_sunset      = document.getElementById('shzm_sunset');
	const sz_habdala     = document.getElementById('shzm_habdala');

	// Weekday
  const shema        = todaySet[0];
  const minha        = todaySet[1];
  const peleg        = todaySet[2];
  const sunset       = todaySet[3];

  z_date.innerHTML   = date + '<br>';
  z_city.innerHTML   = city + '<br>';
  z_shema.innerHTML  = shema + '<br>';
  z_minha.innerHTML  = minha + '<br>';
  z_peleg.innerHTML  = peleg + '<br>';
  z_sunset.innerHTML = sunset + '<br>';

	// Shabbath
  const sh_engdate    = shabbatSet[0];
  const sh_perasha_en = shabbatSet[1];
  const sh_hebdate    = shabbatSet[2];
  const sh_perasha_he = shabbatSet[3];
  const sh_candles    = shabbatSet[4];
  const sh_sunset     = shabbatSet[5];
  const sh_habdala    = shabbatSet[6];

  sz_date.innerHTML        = sh_engdate + '<br>';
  sz_perasha.innerHTML     = sh_perasha_en + '<br>';
  sz_date_heb.innerHTML    = sh_hebdate + '<br>';
  sz_perasha_heb.innerHTML = sh_perasha_he + '<br>';
  sz_candles.innerHTML     = sh_candles + '<br>';
  sz_sunset.innerHTML      = sh_sunset + '<br>';
  sz_habdala.innerHTML     = sh_habdala;
}

// Make sure we're ready to run our script!
jQuery(document).ready(function($) {
  getLocation();
});
