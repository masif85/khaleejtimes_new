jQuery.noConflict();
(function($){

$(".cinema-location-filter").click(function(){
        $('.cinema-locations').animate({opacity: 0}, function(){
                   $('.cinema-locations').removeClass('active');
                   jQuery(".cinema-locations").html('');
                }).end();
        var type=jQuery(this).attr("data-target");
        jQuery.ajax({
        url: "http://localhost/KT-Site/get_cinema.php",
        type: "POST",
        data: {type:type},  
        dataType: "json",       
        success: function (data) {
            jQuery('.cinema-locations').animate({opacity: 1}, function(){
                
                   jQuery('.cinema-locations').addClass('active');
                   jQuery(".cinema-locations").html(data);
                }).end();
        j
         // alert(data);
        }
    });
    });
        
        
jQuery(document).ready(function(){
    jQuery.ajax({
        url: "http://localhost/KT-Site/get_cinema.php",
        type: "POST",
        data: {type:'all'}, 
        dataType: "json",       
        success: function (data) {
        jQuery(".cinema-locations").html('').html(data);
         // alert(data);
        }
    });
});

})(jQuery)