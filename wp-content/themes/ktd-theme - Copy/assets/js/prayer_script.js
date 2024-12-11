/*******************
 This script gets the next pray to be offered.
 The pray time will come from some database or file, this script gets the current time of client machine and then compares with the prayers timings
 and based on those timings updates the area which have the id "pray_time" and display Pray time.

 Note: The hours and minutes for pray will be based on 24 Hour clock.
 **************/
function RunClientPrayerTimes(AWhere)
{
  if (NCS_fajar_time || NCS_fajar_timeRight) {
    var currentYear = (new Date).getFullYear();
    var currentMonth = (new Date).getMonth();
    var currentDay = (new Date).getDate();
    var currentHour = (new Date).getHours();
    var currentMinute = (new Date).getMinutes();
    var currentSeconds = (new Date).getSeconds();

    var LPrayerNameID = "pray_time_js";
    if (AWhere == 1) {
      var fajar_time   = NCS_fajar_time
      var duhar_time   = NCS_duhar_time
      var asr_time     = NCS_asr_time
      var maghrib_time = NCS_maghrib_time
      var isha_time    = NCS_isha_time
    } else {
      var fajar_time   = NCS_fajar_timeRight
      var duhar_time   = NCS_duhar_timeRight
      var asr_time     = NCS_asr_timeRight
      var maghrib_time = NCS_maghrib_timeRight
      var isha_time    = NCS_isha_timeRight
      LPrayerNameID = "pray_time_jsRight";
    }

    var current_time = new Date(currentYear,(currentMonth+1),currentDay,currentHour,currentMinute,currentSeconds);
    var next_prayer = new Date(currentYear,(currentMonth+1),currentDay,currentHour,currentMinute,currentSeconds);

    var hr_difference=0;
    var min_difference=0;

    var time_diff=0;

    var time_in_minute_pray=0;
    var time_in_minute_current=0;

    /**Add zero if inecessary******/
    var fajar_minutes=fajar_time.getMinutes()
    var duhr_minutes=duhar_time.getMinutes()
    var asr_minutes=asr_time.getMinutes()
    var maghrib_minutes=maghrib_time.getMinutes()
    var isha_minutes=isha_time.getMinutes()

    if (fajar_minutes < 10) {
      fajar_minutes="0"+fajar_minutes;
    }

    if (duhr_minutes < 10) {
      duhr_minutes="0"+duhr_minutes;
    }

    if (asr_minutes < 10) {
      asr_minutes="0"+asr_minutes;
    }

    if (maghrib_minutes < 10) {
      maghrib_minutes="0"+maghrib_minutes;
    }

    if (isha_minutes < 10) {
      isha_minutes="0"+isha_minutes;
    }

    if (current_time.getTime() <= fajar_time.getTime()) {
      document.getElementById(LPrayerNameID).innerHTML = "Fajr <span>"+fajar_time.getHours()+":"+fajar_minutes+"</span>";
      hr_difference=fajar_time.getHours()-current_time.getHours();
      min_difference=fajar_time.getMinutes()-current_time.getMinutes();

      time_diff=(fajar_time-current_time);

      time_in_minute_pray=(fajar_time.getHours()*60)+fajar_time.getMinutes();

      next_prayer = fajar_time;
    } else if (current_time.getTime() <= duhar_time.getTime()) {
      document.getElementById(LPrayerNameID).innerHTML = "Dhuhr <span>"+duhar_time.getHours()+":"+duhr_minutes+"</span>";
      hr_difference=duhar_time.getHours()-current_time.getHours();
      min_difference=duhar_time.getMinutes()-current_time.getMinutes();

      time_diff=(duhar_time-current_time);

      time_in_minute_pray=(duhar_time.getHours()*60)+duhar_time.getMinutes();

      next_prayer = duhar_time;
    } else if (current_time.getTime() <= asr_time.getTime()) {
      document.getElementById(LPrayerNameID).innerHTML = "Asr <span>"+asr_time.getHours()+":"+asr_minutes+"</span>";
      hr_difference=asr_time.getHours()-current_time.getHours();
      min_difference=asr_time.getMinutes()-current_time.getMinutes();

      time_diff=(asr_time-current_time);

      time_in_minute_pray=(asr_time.getHours()*60)+asr_time.getMinutes();

      next_prayer = asr_time;
    } else if (current_time.getTime() <= maghrib_time.getTime()) {
      document.getElementById(LPrayerNameID).innerHTML = "Maghrib <span>"+maghrib_time.getHours()+":"+maghrib_minutes+"</span>";
      hr_difference=maghrib_time.getHours()-current_time.getHours();
      min_difference=maghrib_time.getMinutes()-current_time.getMinutes();

      time_diff=(maghrib_time-current_time);

      time_in_minute_pray=(maghrib_time.getHours()*60)+maghrib_time.getMinutes();

      next_prayer = maghrib_time;
    } else if (current_time.getTime() <= isha_time.getTime()) {
      document.getElementById(LPrayerNameID).innerHTML = "Isha <span>"+isha_time.getHours()+":"+isha_minutes+"</span>";
      hr_difference=isha_time.getHours()-current_time.getHours();
      min_difference=isha_time.getMinutes()-current_time.getMinutes();

      time_diff=(isha_time-current_time);

      time_in_minute_pray=(isha_time.getHours()*60)+isha_time.getMinutes();

      next_prayer = isha_time;
    } else {
      document.getElementById(LPrayerNameID).innerHTML = "Fajr <span>"+fajar_time.getHours()+":"+fajar_minutes+"</span>";
      fajar_time.setHours(fajar_time.getHours() + 24);
      hr_difference=fajar_time.getHours()-current_time.getHours();
      min_difference=fajar_time.getMinutes()-current_time.getMinutes();
      
      time_diff=(fajar_time-current_time);
      time_in_minute_pray=(fajar_time.getHours()*60)+fajar_time.getMinutes();

      next_prayer = fajar_time;
    }

    time_in_minute_current=(current_time.getHours()*60)+current_time.getMinutes();
    
    var time_to_next_prayer = next_prayer - current_time;
    var time_to_next_prayer_hh = Math.floor(time_to_next_prayer / 1000 / 60 / 60);

    time_to_next_prayer -= time_to_next_prayer_hh * 1000 * 60 * 60;
    var time_to_next_prayer_mm = Math.floor(time_to_next_prayer / 1000 / 60);

    time_to_next_prayer -= time_to_next_prayer_mm * 1000 * 60;
    var time_to_next_prayer_ss = Math.floor(time_to_next_prayer / 1000);

    if (time_to_next_prayer_hh < 10) {
      time_to_next_prayer_hh="0"+time_to_next_prayer_hh;
    }

    if (time_to_next_prayer_mm < 10) {
      time_to_next_prayer_mm="0"+time_to_next_prayer_mm;
    }

    if (time_to_next_prayer_ss < 10) {
      time_to_next_prayer_ss="0"+time_to_next_prayer_ss;
    }

    if (AWhere == 1) {
      document.getElementById("pray_time_js_H").innerHTML = time_to_next_prayer_hh;
      document.getElementById("pray_time_js_M").innerHTML = time_to_next_prayer_mm;
      document.getElementById("pray_time_js_S").innerHTML = time_to_next_prayer_ss;
    } else {
      document.getElementById("pray_time_jsRight_H").innerHTML = time_to_next_prayer_hh;
      document.getElementById("pray_time_jsRight_M").innerHTML = time_to_next_prayer_mm;
      document.getElementById("pray_time_jsRight_S").innerHTML = time_to_next_prayer_ss;
    }
  }
}
