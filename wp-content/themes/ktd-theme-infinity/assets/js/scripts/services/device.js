if(xdLocalStorage!=null)
	{
xdLocalStorage.init(
				{
				iframeUrl:'https://api.khaleejtimes.com/cross/iframe.html',
				initCallback: function () {				
				xdLocalStorage.getItem('device_uuid', function (data) {
					if(data.value)
					{
						localStorage.setItem("device_uuid",data.value)
						setCookie("device_uuid",data.value,365);						
					}
					else if(localStorage.getItem("device_uuid"))
					{						
						xdLocalStorage.setItem('device_uuid', localStorage.getItem("device_uuid"));		
						setCookie("device_uuid",localStorage.getItem("device_uuid"),365);											
					}
					else					
					{
						uuid=generateUUID();
						xdLocalStorage.setItem('device_uuid', uuid);	
						localStorage.setItem('device_uuid', uuid);
						setCookie("device_uuid",uuid,365);						
					}
						});				
					}
				}
		);	
	function generateUUID(){
		var d = new Date().getTime();
		var uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
			var r = (d + Math.random()*16)%16 | 0;
			d = Math.floor(d/16);
			return (c=='x' ? r : (r&0x3|0x8)).toString(16);
		});
		return uuid;
	};
	function setCookie(name,value,days) {
		var expires = "";
		if (days) {
			var date = new Date();
			date.setTime(date.getTime() + (days*24*60*60*1000));
			expires = "; expires=" + date.toUTCString();
		}
		document.cookie = name + "=" + (value || "")  + expires + "; path=/";
	}
	}