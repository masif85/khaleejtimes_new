
var BASEURL = "https://site.stage.ew.ktd.infomaker.io/scores";


function GetURLParameter(sParam)
{
    var sPageURL = window.location.search.substring(1);
    var sURLVariables = sPageURL.split('&');
    for (var i = 0; i < sURLVariables.length; i++) 
    {
        var sParameterName = sURLVariables[i].split('=');
        if (sParameterName[0] == sParam) 
        {
            return sParameterName[1];
        }
    }
}

var idz = GetURLParameter('id');
var widgetz = GetURLParameter('widget');
var fieldz = GetURLParameter('field');




	function goFullScreen(elementid,type)
		{
			var elem = document.getElementById(elementid);

			if(elem.requestFullscreen){
				elem.requestFullscreen();
			}
			else if(elem.mozRequestFullScreen){
				elem.mozRequestFullScreen();
			}
			else if(elem.webkitRequestFullscreen){
				elem.webkitRequestFullscreen();
			}
			else if(elem.msRequestFullscreen){
				elem.msRequestFullscreen();
			}
		}

	function hitthetab(divid,tabnumber)
		{
			//console.log("#"+divid+" li:nth-child("+tabnumber+") a");
			$( "#"+divid+" li:nth-child("+tabnumber+") a" ).trigger("click");
			
				setTimeout(function(){ $( "#"+divid+" li:nth-child("+tabnumber+") a" ).trigger("click"); },3000);
				setTimeout(function(){ $( "#"+divid+" li:nth-child("+tabnumber+") a" ).trigger("click"); },5000);
				setTimeout(function(){ $( "#"+divid+" li:nth-child("+tabnumber+") a" ).trigger("click"); },10000);
		}

function setktCookie(e, t, o) {
    var i = new Date;
    i.setTime(i.getTime() + 24 * o * 60 * 60 * 1e3);
    var r = "expires=" + i.toUTCString();
    return document.cookie = e + "=" + t + ";" + r + ";path=/", t
}


	
var issliderwidgetontop = document.getElementById("sliderwidgetontop");
	if(issliderwidgetontop)
		{
			Entity_sport.push({
				code: "4654436544",
				field: "entity_cricket",
				widget_type: "content_type",
				widget: "slider_widget",
				id: "undefined",
				more_one: "",
				widget_size: "large",
				where_to: "sliderwidgetontop",
				base_path: BASEURL,
				links: "1",
				color_type: "light",
				choosed_color: "",
				choosed_preset: "",
				});
		}  
	
	var isiplpointtable = document.getElementById("iplpointtable");
	if(isiplpointtable)
		{
			Entity_sport.push({
				code: "4654436544",
				field: "entity_cricket",
				widget_type: "content_type",
				widget: "competetion_standings",
				id: "116699",
				more_one: "",
				widget_size: "large",
				where_to: "iplpointtable",
				base_path: BASEURL,
				links: "1",
				color_type: "light",
				choosed_color: "",
				choosed_preset: "",
				});
		}
		
	var isiplfixturediv = document.getElementById("iplfixturediv");
	if(isiplfixturediv)
		{
			
			
			Entity_sport.push({
				code: "4654436544",
				field: "entity_cricket",
				widget_type: "content_type",
				widget: "fixtures",
				id: "undefined",
				more_one: "",
				widget_size: "large",
				where_to: "iplfixturediv",
				base_path: BASEURL,
				links: "1",
				color_type: "light",
				choosed_color: "",
				choosed_preset: "",
				});
			
		} 
		

var ismatchcenter = document.getElementById("matchcenterdiv");
	if(ismatchcenter)
		{
// matchcenterdiv?field=entity_cricket&id=45141&widget=match_center
			Entity_sport.push({
			code: "4654436544",
			field: fieldz,
			widget_type: "content_type",
			widget: widgetz,
			id: idz,
			more_one: "",
			widget_size: "large",
			where_to: "matchcenterdiv",
			base_path: BASEURL,
			links: "1",
			color_type: "light",
			choosed_color: "",
			choosed_preset: "",
			});
		}
		
		
		
var isfixturesdiv = document.getElementById("fixturesdiv");
if(isfixturesdiv)
	{	



	Entity_sport.push({
		code: "4654436544",
		field: "entity_cricket",
		widget_type: "content_type",
		widget: "fixtures",
		id: "undefined",
		more_one: "",
		widget_size: "large",
		where_to: "fixturesdiv",
		base_path: BASEURL,
		links: "1",
		color_type: "light",
		choosed_color: "",
		choosed_preset: "",
		});

		
/*
Entity_sport.push({
			code: "4654436544",
			field: "entity_cricket",
			widget_type: "content_type",
			widget: "competetion_feature",
			id: "116699",
			more_one: "",
			widget_size: "large",
			where_to: "fixturesdiv",
			base_path: BASEURL,
			links: "1",
			color_type: "light",
			choosed_color: "",
			choosed_preset: "",
			});

		Entity_sport.push({
			code: "4654436544",
			field: "entity_cricket",
			widget_type: "content_type",
			widget: "fixtures",
			id: "45536",
			more_one: "",
			widget_size: "small",
			where_to: "fixturesdiv",
			base_path: BASEURL,
			links: "1",
			color_type: "light",
			choosed_color: "",
			choosed_preset: "",
			});
	*/		
	}
	
var isfixturesdivipl2020 = document.getElementById("fixturesdivipl2020");
if(isfixturesdivipl2020)
	{	
	
	Entity_sport.push({
		code: "4654436544",
		field: "entity_cricket",
		widget_type: "content_type",
		widget: "fixtures",
		id: "undefined",
		more_one: "",
		widget_size: "large",
		where_to: "fixturesdivipl2020",
		base_path: BASEURL,
		links: "1",
		color_type: "light",
		choosed_color: "",
		choosed_preset: "",
		});
		
			/*
					Entity_sport.push({
						code: "4654436544",
						field: "entity_cricket",
						widget_type: "content_type",
						widget: "competetion_feature",
						id: "116699",
						more_one: "",
						widget_size: "large",
						where_to: "fixturesdivipl2020",
						base_path: BASEURL,
						links: "1",
						color_type: "light",
						choosed_color: "",
						choosed_preset: "",
						});
			*/
	
	}
	
var ismobilefixturesdiv = document.getElementById("mobilefixturesdiv");
if(ismobilefixturesdiv)
	{	
		Entity_sport.push({
			code: "4654436544",
			field: "entity_cricket",
			widget_type: "content_type",
			widget: "fixtures",
			id: "45536",
			more_one: "",
			widget_size: "small",
			where_to: "mobilefixturesdiv",
			base_path: BASEURL,
			links: "1",
			color_type: "light",
			choosed_color: "",
			choosed_preset: "",
			});
	}	
	// <div id="whereUwantToPutOnlyIdcompetetion_center"></div>
var ishomelatestseries = document.getElementById("homelatestseries");
	if(ishomelatestseries)
		{	
	
					Entity_sport.push({
						code: "4654436544",
						field: "entity_cricket",
						widget_type: "content_type",
						widget: "competetion_center",
						id: "116699",
						more_one: "",
						widget_size: "small",
						where_to: "homelatestseries",
						base_path: BASEURL,
						links: "1",
						color_type: "light",
						choosed_color: "",
						choosed_preset: "",
					});
		}
		
var iswhereUwantToPutOnlyIdseries_castrol_info = document.getElementById("whereUwantToPutOnlyIdseries_castrol_info");
	if(iswhereUwantToPutOnlyIdseries_castrol_info)
		{
			
			var iplid	=	$("#whereUwantToPutOnlyIdseries_castrol_info").attr('iplid');
			
					Entity_sport.push(
							{
								code: "4654436544",
								field: "entity_cricket",
								widget_type: "content_type",
								widget: "series_castrol_info",
								id: iplid, //  116506  116699
								more_one: "",
								widget_size: "large",
								where_to: "whereUwantToPutOnlyIdseries_castrol_info",
								base_path: BASEURL,
								links: "1",
								color_type: "light",
								choosed_color: "",
								choosed_preset: "",
							});
		}
		
		
var isbottomwidgethere = document.getElementById("bottomwidgethere");
	if(isbottomwidgethere)
		{
			
			var matchid	=	$("#bottomwidgethere").attr('matchid');
			console.log(matchid);
					Entity_sport.push({
							code: "4654436544",
							field: "entity_cricket",
							widget_type: "content_type",
							widget: "singlebttom_widget",
							id: matchid, // 45589
							more_one: "batting_most_run50",
							widget_size: "large",
							where_to: "bottomwidgethere",
							base_path: BASEURL,
							links: "1",
							color_type: "light",
							choosed_color: "",
							choosed_preset: "",
						});
		}
		
		/*
					Entity_sport.push({
					code: "4654436544",
					field: "entity_cricket",
					widget_type: "content_type",
					widget: "competetion_center",
					id: "116507",
					more_one: "",
					widget_size: "small",
					where_to: "homelatestseries",
					base_path: BASEURL,
					links: "1",
					color_type: "light",
					choosed_color: "",
					choosed_preset: "",
					});
				*/
	

				
				