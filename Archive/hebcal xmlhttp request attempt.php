<?php ?>
<script>
    var awesome = document.getElementById("locationAwesome");
// Output date in correct format
    function formatDate() {
        var d = new Date();
        var month = '' + (d.getMonth() + 1);
        var day = '' + d.getDate();
        var year = d.getFullYear();
        // console.log(d, month, day, year);

        if (month.length <2) month = '0' + month;
        if (day.length < 2) day = '0' + day;

        return [year, month, day].join('-');
    }

    var yom_ymd = formatDate();
    // console.log("yom_ymd = ", yom_ymd);

    let fileContents = null;
    function readContents(events) {
        fileContents = this.responseText;
        // console.log(fileContents);
    }

    // Get the Data from the Hebcal URL
    let xmlhttp = new XMLHttpRequest();
    xmlhttp.addEventListener("load", readContents, true);
    awesome.addEventListener("load", (e) => {console.log("Event", e)});
    xmlhttp.open("GET", "https://www.hebcal.com/converter/?cfg=json&gy=2018&gm=3&gd=19&g2h=1", true);
    xmlhttp.send();
    // console.log(xmlhttp);

    // Not the ideal solution
    function doWork() {
        if (fileContents === null) {
            window.setTimeout(doWork, 100);
            return;
        }
        // the hebDateStr here is an unnecessary extra step
        // steps could be combined
        var hebDate = JSON.parse(fileContents);
        var hebDateStr = hebDate["hebrew"];
        console.log(hebDateStr);
        awesome.innerHTML = "Hebrew Date: " + hebDateStr;
    }
    doWork();
</script>