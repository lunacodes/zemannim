<?php

    $lat = $data['8'];
    $long = $data['9'];
    $city =  $data['6'];
    $state =  $data['5'];
    $country =  $data['4'];
    $address = "$city, $state $country";

?>
<script>
    /* Time Zone Section */
    var tzAwesome = document.getElementById("Awesome2");
    let tzContents = null;
    function readTzContents(event) {
        tzContents = this.responseText;
        console.log("tzContents", tzContents);
    }

    // jQuery(document).ready(function($) {
    //     $.ajax({
    //         url: "https://api.geonames.org/timezoneJSON?lat=40.7143&lng=-74.006&date=2018-03-20&username=lunacodes",
    //         headers: {
    //             'Access-Control-Allow-Origin': 'https://api.geonames.org'
    //         },
    //         success: function(data) {
    //             // console.log($(data));
    //             console.log("Success Yo!");
    //         }
    //     });
    // });

    // let xmltzc = new XMLHttpRequest();
    // xmltzc.addEventListener("load", readTzContents, false);
    // tzAwesome.addEventListener("load", (e) => {console.log(e)});
    // xmltzc.open("GET", "http://api.geonames.org/timezoneJSON?lat=40.7143&lng=-74.006&date=2018-03-20&username=lunacodes", true);
    // xmltzc.withCredentials = true;
    // xmltzc.send();
    
    // function tzcDoWork() {
    //     if (tzContents === null) {
    //         window.setTimeout(tzcDoWork, 100);
    //         return;
    //     }
    //     console.log(tzContents);
    // }
    // tzcDoWork();

    /* Note: I need the data from the Hebcal function */
    var timeZoneUrl = 'http://api.geonames.org/timezoneJSON?lat=40.7143&lng=-74.006&date=2018-03-20&username=lunacodes ';
    /* Put in xmlHttp code to actually process the url here
    Instead of pretending like I have the data (as I'm doing rn) */
    var tzJd = {"sunrise":"2018-03-20 06:59","lng":-74.006,"countryCode":"US","gmtOffset":-5,"rawOffset":-5,"sunset":"2018-03-20 19:07","timezoneId":"America/New_York","dstOffset":-4,"dates":[{"date":"2018-03-20","sunrise":"2018-03-20 06:59","sunset":"2018-03-20 19:07"}],"countryName":"United States","time":"2018-03-20 14:37","lat":40.7143};
    // console.log("tzJd: ", tzJd);
    var tzName = tzJd['timezoneId'];
    // var offSet = tzJd['gmtOffset'];
    var offSet;
    var yomSunrise = tzJd['dates'][0]['sunrise'];
    var yomSunset = tzJd['dates'][0]['sunset'];
    console.log("Yom Sunrise: ", yomSunrise, '\n',  " Yom Sunset: ", yomSunset);
