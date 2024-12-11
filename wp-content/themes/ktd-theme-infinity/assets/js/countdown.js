jQuery.noConflict();
(function($){

    $(function() {
        function getCounterData(obj) {
            //var days = parseInt($('.e-m-days', obj).text());
            var hours = parseInt($('.e-m-hours', obj).first().text());
            var minutes = parseInt($('.e-m-minutes', obj).first().text());
            var seconds = parseInt($('.e-m-seconds', obj).first().text());
            return seconds + (minutes * 60) + (hours * 3600);
        }

        function zeroPad(num, places) {
            var zero = places - num.toString().length + 1;
            return Array(+(zero > 0 && zero)).join("0") + num;
        }

        function setCounterData(s, obj) {
            //var days = Math.floor(s / (3600 * 24));
            var hours = Math.floor((s % (60 * 60 * 24)) / (3600));
            var minutes = Math.floor((s % (60 * 60)) / 60);
            var seconds = Math.floor(s % 60);

            //console.log(hours, minutes, seconds);

            //$('.e-m-days', obj).html(days);
            $('.e-m-hours', obj).html(zeroPad(hours, 2));
            $('.e-m-minutes', obj).html(zeroPad(minutes, 2));
            $('.e-m-seconds', obj).html(zeroPad(seconds, 2));
        }



        var count = getCounterData($(".counter"));

        var timer = setInterval(function() {
            count--;
            if (count == 0) {
                clearInterval(timer);
                return;
            }
            setCounterData(count, $(".counter"));
        }, 1000);
    });


})(jQuery)