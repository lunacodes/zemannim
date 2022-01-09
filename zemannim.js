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

  // If HTML 5 Geolocation permission enabled
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      getGeoDetails,
      getAddrDetailsByIp,
      options
    );
  }
}

/**
 * Fetch and parse JSON object from DB-IP API, and passes
 * lat, long, and city strings to hebCalShab
 */
function getAddrDetailsByIp() {
  // Get the user's ip & location info
  const urlStr = 'https://ipapi.co/json/';
  fetch(urlStr)
    .then( response => {
      return response.json();
    })
    .then( res => {
      const state     = res.region_code;
      const city      = res.city;
      const cityStr   = `${city}, ${state}`;
      const lat       = res.latitude;
      const long      = res.longitude;
      const tzid      = res.timezone ;

      hebCalShab({cityStr, lat, long, tzid});
    });
}

/**
 * Feeds lat & long coords into Google Maps API to obtain city and state info and
 * pass to generateTimes
 *
 * @param  {object | float } User's lattitude and longitude coords
 */
function getGeoDetails(pos) {
  const lat    = pos.coords.latitude;
  const long   = pos.coords.longitude;
  let city     = '';
  let cityStr  = '';
  let state    = '';

  const point  = new google.maps.LatLng(lat, long);
  new google.maps.Geocoder().geocode(
    {'latLng': point},
    (res, status) => {
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

      getTimeZoneID({cityStr, lat, long, utc});
    }
  );
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
 * @param  {object}  User's location and timezone info
 *   {string} city   User city and state/locality
 *   {number} lat    User lattitude coordinates
 *   {number} long   User longitude coordinates
 *   {string} utc    UTC string containing user's local time info
 */
function getTimeZoneID(locObj) {
  const tzKey     = 'AIzaSyDgpmHtOYqSzG9JgJf98Isjno6YwVxCrEE';
  const tzUrlBase = 'https://maps.googleapis.com/maps/api/timezone/json?location=';
  const tzUrlStr  = tzUrlBase + locObj.lat + ',' + locObj.long + '&timestamp=' + locObj.utc + '&key=' + tzKey;

  fetch(tzUrlStr)
    .then( (response) => {
      return response.json();
    })
    .then( (res) => {
      locObj.tzid = res.timeZoneId;
      hebCalShab(locObj);
    }
  );
}

/**
 * Fetches Hebcal JSON data to use in time calculations, and generates strings for dates and times.
 * Provides the main routing for the rest of the code
 * @param  {object}  User's location and timezone info
 *   {string} city   User city and state/locality
 *   {number} lat    User lattitude coordinates
 *   {number} long   User longitude coordinates
 *   {string} utc    UTC string containing user's local time info
 */
function hebCalShab(locObj) {
  const now         = new Date();
  let month         = now.getMonth() + 1;
  const year        = now.getFullYear();
  const daysInMonth = getDaysInMonth(month, year);

  // If today's the last day of the month, advance to next month
  const day_num     = now.getDate();
  if ( daysInMonth === day_num ) {
    month = month + 1;
  }

  const urlStr = 'https://www.hebcal.com/hebcal/?v=1&cfg=json&maj=on&min=on&nx=on&ss=on&mod=off&s=on&c=on&m=20&b=18&o=on&D=on&year=now&month=' + month + '&i=off&geo=pos' + '&latitude=' + locObj.lat + '&longitude=' + locObj.long + '&tzid=' + locObj.tzid;

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
      const shabbatDate       = getShabbatDates(todayNum, todayDate);
      const fri               = shabbatDate.fri;
      const sat               = shabbatDate.sat;

      // Shabbath Strings
      const shabbatData       = getShabbatInfo(data, fri, sat);
      const dateStr           = fri.toLocaleString('en-us', { month: 'long', day: 'numeric', year: 'numeric' });
      const engdate           = 'Shabbat Times for ' + dateStr;
      const hebdate           = shabbatData.hebrewDate;

      // Candles & Sunset
      const candles           = shabbatData.candlesTitle;
      let sunset              = getFriSunsetTime(candles);
      sunset                  = 'Sunset: ' + sunset;

      // Habdala Info
      let habdala             = shabbatData.habdalaTitle;
      const index             = habdala.indexOf( '(' ) - 1;
      habdala                 = 'Haḇdala' + habdala.slice(index);

      // Perasha Info
      const perasha           = shabbatData.perashaObj;
      let perashaHeb          = perasha.hebrew;
      let perashaEng          = perasha.title;
      const phIndex           = perashaHeb.indexOf('ת') + 1;
      perashaHeb              = 'פרשה' + perashaHeb.slice(phIndex);

      // Transliterations
      const firstSpace        = perashaEng.indexOf(' ');
      const perashaShort      = perashaEng.slice(firstSpace + 1);
      const a2s               = ashkiToSeph(perashaShort, 'p');
      perashaEng              = 'Perasha ' + a2s;

      const shabbatSet        = {engdate, perashaEng, hebdate, perashaHeb, candles, sunset, habdala};
      const todaySet          = timesHelper(locObj.lat, locObj.long);
      const cityStr           = locObj.cityStr;

      displayTimes({todaySet, shabbatSet, cityStr, todayStr});
  });
}

/**
 * Removes leading 0 from 2-digit month or date numbers and returns to generateTimeStrings
 * e.g.
 * @param  {number} x  Month number passed from generateSunStrings
 * @return {number}    Month number sans leading zero
 */
function formatTime(x) {
  let reformattedTime = x.toString();
  reformattedTime     = ('0' + x).slice(-2);

  return reformattedTime;
}

/**
 * Converts a UTC timestamp into H:M AM/PM
 * @param  {number} timestamp UTC Timestamp
 * @return {string}           Time in H:M AM/PM
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
 * @param  {number} lat   User's lattitude coordinates
 * @param  {number} long  User's longitude coordinates
 * @return {string}       Time String in Y-M-D-H-S
 */
function timesHelper(lat, long) {
  const todayTimesObj  = SunCalc.getTimes(new Date(), lat, long);
  const todayTimes     = calculateSunTimes(todayTimesObj);
  const todayStrSet    = generateTimeStrings(todayTimes);

  return todayStrSet;
}

/**
 * Helper function for hebCalShabbat. Determines if it's the last day of the month,
 * so the widget does not prematurely display an extra month ahead
 *
 * @param  {number} month  current month number
 * @param  {number} year   current year number
 * @return {Date}       number of days in the month
 */
function getDaysInMonth(month, year) {
  return new Date(year, month, 0).getDate();
}

/**
 * Calculates the upcoming Shabbat's date, by determining the next Friday and Saturday
 * @param  {number} dayNum          The number day of the week
 * @param  {object | Date} dayDate  Today's date
 * @return {object}                 Start and end date for Shabbat
 */
function getShabbatDates(dayNum, dayDate) {
  if (dayNum < 7) {
    const adjust  = 7 - dayNum;
    const dateMod = dayDate + (adjust);
    const fri     = new Date();
    const sat     = new Date();

    fri.setDate(dateMod - 2);
    sat.setDate(dateMod - 1);

    return { fri, sat };
  }
}

/**
 * Outputs Hebrew Shabbat date, candle lighting and Habdala times, and perasha name
 * @param  {Object} data      Hebcal JSON data
 * @param  {Object} friday    Date object with Friday's date
 * @param  {Object} saturday  Date object with Saturday's date
 * @return {Object}           hebrewDate, candlesTitle, habdalaTitle, and perashaObj
 */
function getShabbatInfo(data, friday, saturday) {
  let candlesList   = [];
  let hebDatesList  = [];
  let habdalaTimes  = [];
  let perashaList   = [];
  const fri         = new Date(friday);
  const sat         = new Date(saturday);

  data.forEach((item) => {
    const d2        = new Date(item.date);
    const same_fri  = fri.getDate() === d2.getDate();
    const same_sat  = sat.getDate() === d2.getDate();

    if ((item.category === 'hebdate') && same_fri) {
      hebDatesList.push(item);
    }

    if ((item.category === 'candles') && same_fri) {
      candlesList.push(item);
    }

    if ((item.category === 'havdalah') && same_sat) {
      habdalaTimes.push(item);
    }

    if ((item.category === 'parashat') && same_fri ) {
      perashaList.push(item);
    }
  });


  const shabbat_data = {
    "hebrewDate"       : hebDatesList[0].hebrew,
    "candlesTitle"     : candlesList[0].title,
    "habdalaTitle"     : habdalaTimes[0].title,
    "perashaObj"       : perashaList[0]
  };

  return shabbat_data;
}

/**
 * Calculate's Friday's sunset time to use for further string and time calculations
 * @param  {string} timeStr  Title string containing the candle lighting time. Not a Date object!
 * @return {string}          Friday's sunset time
 */
function getFriSunsetTime(timeStr) {
  const time = timeStr.replace(/\D/g,'');
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
 * Converts perasha and holiday names from Ashkenazi to Sepharadi transliteration
 * @param  {string} input  Name of perasha from Hebcal API
 * @param  {string} sel    Determines if we're parsing a perasha or holidays
 * @return {string}        Transliterated perasha or holiday name
 */
function ashkiToSeph(input, sel) {
  /* jshint quotmark:false */
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
    ["Rosh Chodesh %s", "Rosh Ḥoḏesh %s"],
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

  const str = input.replace(/\w\S*/g, txt => { return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase(); });

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
 * Performs the preliminary solar calculations and convesions
 * used by calculateZemannim for sha'oth zemanniyoth
 * @param  {array} timeObj   Suncalc times, based on current date and user's lat & long coords.
 *   See Suncalc.getTimes docs for more info.
 * @return {object}          Contains the floats used by calculateZemmanim
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

  return {sunriseSec, sunsetSec, offSetSec, sunsetDateTimeInt};
}

/**
 * Calculates the latest Shema, earliest Minha, and Peleg haMinha times.
 * Passes back the finalized times strings to generateTimesStrings
 * @param  {[object]} timeSet  prayer times and Suncalc offset
 * @return {[object]}          Contains shema, earlyMinha, pelegMinha, and sunsetFinal
 */
function calculateZemannim(timeSet) {
  const sunrise       = timeSet.sunrise;
  const sunset        = timeSet.sunset;
  const offSet        = timeSet.offSet;
  const sunsetFinal   = unixTimestampToDate(timeSet.sunsetDateTimeInt + offSet);

  // Latest Shema
  const shemaHr       = Math.abs((sunset - sunrise) / 12);
  const shemaSec      = sunrise + (shemaHr * 3) + offSet;
  const shema         = unixTimestampToDate(shemaSec);

  // Minha
  const minhaHr       = (sunset - sunrise) / 12;

  // Earliest Minha
  const earlyMinhaSec = sunrise + (minhaHr * 6.5) + offSet;
  const earlyMinha    = unixTimestampToDate(earlyMinhaSec);

  // Peleg haMinha
  const pelegMinhaSec = sunset - (minhaHr * 1.25) + offSet;
  const pelegMinha    = unixTimestampToDate(pelegMinhaSec);

  return {shema, earlyMinha, pelegMinha, sunsetFinal};
}

/**
 * Helper function for calculateSunTimes
 * Splits timeObj into Y, M, D, H, M and returns a formatted string.
 * Used by displayTimes to generate the users views on the page
 * @param  {number} timeObj Value passed in from calculateSunTimes
 * @return {string}         Time String in Y-M-D H:M format
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
 * Final step in timesHelper, before the info is passed on to displayTimes
 * @param  {number} timeObj   Value passed from generateTimes after formatting from formatTime
 * @return {object}           latestShemaStr, earliestMinhaStr, pelegHaMinhaStr, sunsetStr
 */
function generateTimeStrings(timeSet) {
  const sunrise           = timeSet.sunriseSec;
  const sunset            = timeSet.sunsetSec;
  const offSet            = timeSet.offSetSec;
  const sunsetDateTimeInt = timeSet.sunsetDateTimeInt;

  const zmTimes = calculateZemannim({sunrise, sunset, offSet, sunsetDateTimeInt});

  const latestShemaStr    = '<span class="zemannim-label">Latest Shema: </span>' + zmTimes.shema;
  const earliestMinhaStr  = '<span class="zemannim-label">Earliest Minḥa: </span>' + zmTimes.earlyMinha;
  const pelegHaMinhaStr   = '<span class="zemannim-label">Peleḡ HaMinḥa: </span>' + zmTimes.pelegMinha;
  const sunsetStr         = '<span class="zemannim-label">Sunset: </span>' + zmTimes.sunsetFinal;

  return {latestShemaStr, earliestMinhaStr, pelegHaMinhaStr, sunsetStr};
}

/**
 * Receives time and location info from generateTimes and
 * writes innerHtml for front-end display, via jQuery
 * @param  {object} strSet  The final set of strings that will be displayed to the user
 */
function displayTimes(strSet) {
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

  const todaySet       = strSet.todaySet;
  const shabbatSet     = strSet.shabbatSet;
  const city           = strSet.cityStr;
  const date           = strSet.todayStr;

  // Weekday
  z_date.innerHTML         = date + '<br>';
  z_city.innerHTML         = city + '<br>';
  z_shema.innerHTML        = todaySet.latestShemaStr + '<br>';
  z_minha.innerHTML        = todaySet.earliestMinhaStr + '<br>';
  z_peleg.innerHTML        = todaySet.pelegHaMinhaStr + '<br>';
  z_sunset.innerHTML       = todaySet.sunsetStr + '<br>';

  // Shabbath
  sz_date.innerHTML        = shabbatSet.engdate + '<br>';
  sz_perasha.innerHTML     = shabbatSet.perashaEng + '<br>';
  sz_date_heb.innerHTML    = shabbatSet.hebdate + '<br>';
  sz_perasha_heb.innerHTML = shabbatSet.perashaHeb + '<br>';
  sz_candles.innerHTML     = shabbatSet.candles + '<br>';
  sz_sunset.innerHTML      = shabbatSet.sunset + '<br>';
  sz_habdala.innerHTML     = shabbatSet.habdala;
}

// Make sure we're ready to run our script!
jQuery(document).ready( () => {
  getLocation();
});
