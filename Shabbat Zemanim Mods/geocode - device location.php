<!-- Load Google Geocode API -->
<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyDFrCM7Ao83pwu_avw-53o7cV0Ym7eLqpc" type="text/javascript"></script>

<!-- Get device Latitude & Longitude via Geocode -->
<script type="text/javascript">
    var x = document.getElementById("location");
    function getLocation() {
        'use strict';
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(showPosition);
        }

        else {
            console.log("Geolocation is not supported by this browser");
        }
    }
    
    function showPosition(position) {
        'use strict';
        x.innerHTML = "Latitude: " + position.coords.latitude +
            "<br>Longitutde: " + position.coords.longitude;
    }

    getLocation();
    showPosition();
</script>


<?php
    $ip = '';
    $ip  = !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
    echo("IP is: $ip");

    ?>
    <div id="location"></div>


    <?php
    $url = "http://api.ipinfodb.com/v3/ip-city/?key=15fd47f9a1419a3ab10a17e9f655cfff81ca7278fc86f9337a2dd334d24425fb&ip=$ip";
    $d = file_get_contents($url);        
    $data = explode(';' , $d);
    var_dump($data);
    $lat = $data['8'];
    $long = $data['9'];
    $city =  $data['6'];
    $state =  $data['5'];
    $country =  $data['4'];
    $address = "$city, $state $country";
    // reset ip - not strictly sure if this is necessary?
    $ip = '';

?>