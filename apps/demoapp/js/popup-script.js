
var ispoen = 0;
var isReload=0;

$(document).ready(function() {
	/*
    $('#demoapp').click(function() {
		alert();
        var newwindow = window.open($(this).prop('href'), '', 'height=600,width=600');
        if (window.focus) {
            newwindow.focus();
        }
        return false;
    });

    $('#NewTab').click(function() {
        $(this).target = "_blank";
        window.open($(this).prop('href'));
        return false;
    });
	*/
	
	$("ul#appmenu li").click(function() {
		
		var id =$(this).data('id');
		if(id == 'files')
		{
			if($("#dir").length == 0) 
			{
				var h = $("a",this).attr('href');
				$(location).attr('href', h);
			}
			else
				$("ul.with-icon li:first-child a").trigger("click");
			return false;
		}
		else if(id == 'demoapp')
		{
			var h = $("a",this).attr('href');
			var WindowLeft = (window.screen.availWidth - 700);
			var WindowTop  = (window.screen.availHeight - 500);
			
			var udir = '/';
			var dir = $('#dir').val();
			if (dir == null)
				udir = '/';
			else if (dir == undefined)
				udir = '/';
			else
				udir = dir;
			
			//alert(udir);
			//window.moveTo WindowLeft, WindowTop
			var h = '/nc/index.php/apps/demoapp/'
			//var newwindow = window.open($(this).prop('href'), '', 'height=300,width=600,right=0,bottom=0');
			//var newwindow = window.open(h, '', 'height=300,width=600,right=0,bottom=0');
			
			var newwindow = window.open(h+"?udir="+udir, "customWindow", "width=650, height=400, top="+WindowTop+", left="+WindowLeft);
			
			if (window.focus) {
				newwindow.focus();
			}
			return false;
		}
		else if(id == 'reloadApp')
		{
			var postData = {type:'adddir'};
			$.ajax({
				url: '/nc/index.php/apps/demoapp/ajax/upload.php',
				type: 'POST',
				beforeSend: function(xhr){
					xhr.setRequestHeader('Accept', 'text/plain');
					xhr.setRequestHeader('Authorization', ' Basic YWRtaW46YWRtaW4jMTIz');
				},
				xhr: function() {
					var myXhr = $.ajaxSettings.xhr();
					return myXhr;
				},
				success: function (data) {
					console.log("Data Uploaded:");
					console.log(data);
				
					alert('Mount successfully');
					//window.location.reload();
				},
				data: postData,
				cache: false,
				contentType: false,
				processData: false
			});
			return false;
		}
		else{
			//alert(id);
		}
		
	});
	
});