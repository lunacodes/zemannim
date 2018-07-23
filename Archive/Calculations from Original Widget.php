 <?php 
        $yom = strtotime("now");
        $yom_txt = date("M d, Y", $yom);
        $yom_ymd = date("Y-m-d", $yom);
        echo('$yom = ' . $yom . "<br>");
        echo('$yom_txt = ' . $yom_txt . "<br>");
        echo('$yom_ymd = ' . $yom_ymd . "<br>");
        // echo("Yom YMD: " . $yom_ymd);

        // Get Hebrew Date from HebCal
        // more info: http://www.hebcal.com/home/219/hebrew-date-converter-rest-api
            $hebdate_str = "http://www.hebcal.com/converter/?cfg=json&gy=".date("Y",$yom)."&gm=".date("n",$yom)."&gd=".date("j",$yom)."&g2h=1";
            echo("<br> $hebdate_str <br>");
            $hebdate = file_get_contents("http://www.hebcal.com/converter/?cfg=json&gy=".date("Y",$yom)."&gm=".date("n",$yom)."&gd=".date("j",$yom)."&g2h=1");
            echo($hebdate);
            $hebdatejd = json_decode($hebdate,true);
            // var_dump($hebdatejd);
            $hebengdate = $hebdatejd['hd']." ".$hebdatejd['hm'].", ".$hebdatejd['hy'];
            $hebhebdate = $hebdatejd['hebrew'];
            if($instance['hebdatetxt'] == "e") {$hebrewdate = $hebengdate; } 
            if($instance['hebdatetxt'] == "h") {$hebrewdate = $hebhebdate; } 
            if($instance['hebdatetxt'] == "b") {$hebrewdate = $hebengdate."<br />".$hebhebdate; } 
        
                /* JSON get lat/long from zip */

            $ip = '';
            // $ip  = !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
            $ip = $_SERVER['REMOTE_ADDR'];
            $url = "http://api.ipinfodb.com/v3/ip-city/?key=15fd47f9a1419a3ab10a17e9f655cfff81ca7278fc86f9337a2dd334d24425fb&ip=$ip";
            $d = file_get_contents($url);        
            $data = explode(';' , $d);
            // var_dump($data);
            $lat = $data['8'];
            $long = $data['9'];
            $city =  $data['6'];
            $state =  $data['5'];
            $country =  $data['4'];
            $address = "$city, $state $country";
            // reset ip - not strictly sure if this is necessary?
            // $ip = '';
            // echo("Latitude: $lat <br>Longitude: $long");

        /* Get time offset for timzezone and DST */
        $tzurl = "http://api.geonames.org/timezoneJSON?lat=".$lat."&lng=".$long."&date=".$yom_ymd."&username=lunacodes";
        $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $tzurl);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $tz = curl_exec($ch);
            curl_close($ch);
        $tzjd = json_decode(utf8_encode($tz),true);
        // var_dump($tzjd);
        $tzname = $tzjd['timezoneId'];
        $offset = $tzjd['gmtOffset'];
        $yomsunrise = $tzjd['dates'][0]['sunrise'];
        $yomsunset = $tzjd['dates'][0]['sunset'];
        $yomsunrisedatetime = strtotime($yomsunrise);
        $yomsunsetdatetime = strtotime($yomsunset);
        // echo("<br>" . $yomsunrise . "<br>" .  $yomsunset . "<br>");
        // echo("br");
        // echo("<br>" . $yomsunrisedatetime . "<br>" . $yomsunsetdatetime . "<br>");
        $sunrisesec = $yomsunrisedatetime+$offset;
        $sunsetsec = $yomsunsetdatetime+$offset;
        // echo("<br>Offset: " . $offset . "<br>");
        // echo("<br>Sunrise Sec: " . $sunrisesec . "<br>");
        // echo("<br>Sunset Sec: " . $sunsetsec . "<br>");

        // Show Date & Country Info
        echo("Times for " . $yom_txt . '<br />');
        echo("$address <br />");
        echo($hebhebdate . "<br />");
        // Calculate Halakhic Times
        $timedisplay = "g:i A";

        // Calculate Shema
        $halachichour = ($sunsetsec - $sunrisesec)/12;
        $shemainseconds = $sunrisesec + ($halachichour*3);
        $shematext = '<span id="zmantitle">Latest Shemaʿ: </span>'.date($timedisplay, $shemainseconds).'<br />';
        echo($shematext);

        // Calculate Earliest Minha
        $halachichour = ($sunsetsec - $sunrisesec)/12;
        $minchainseconds = $sunrisesec + ($halachichour*6.5);
        $minchatext = '<span id="zmantitle">Earliest Minḥa: </span>'.date($timedisplay, $minchainseconds).'<br />';
        echo($minchatext);

        // Calculate Plag haMinha
        $halachichour = ($sunsetsec - $sunrisesec)/12;
        $plaginseconds = $sunsetsec - ($halachichour*1.25);
        $plagtext = '<span id="zmantitle">Peleḡ HaMinḥa: </span>'.date($timedisplay, $plaginseconds).'<br />';
        echo($plagtext);

        // echo($address);
        // echo($halachichour);

        // Display Sunset
        echo '<span id="zmantitle">Sunset: </span>'.date($timedisplay,$yomsunsetdatetime).'<br />';