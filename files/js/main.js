$(document).ready(function(){
	
	function initWidthHeight(){
		var inner_width = 0;
		$("#field ul").each(function(){
			inner_width += $(this).width();
			inner_height = $(this).height();
		});
		$("body").css({ "overflow":"hidden" });
		$("#panel").width($("#panel").width()).height($(window).innerHeight() - $("#head").height());
		$("#field").width($(window).innerWidth() - $("#panel").width()).height($(window).innerHeight() - $("#head").height());
		$("#field_inner").width(inner_width).height(inner_height)
		if($("#field_inner").width() < $("#field").width()){
			$("#field_inner").css({ "margin-left":( ($("#field").width() - $("#field_inner").width()) / 2 ) + "px" });
		} else {
			$("#field_inner").css({ "margin-left":0 });
		}
		if($("#field_inner").height() < $("#field").height()){
			$("#field_inner").css({ "margin-top":( ($("#field").height() - $("#field_inner").height()) / 2 ) + "px" });
		} else {
			$("#field_inner").css({ "margin-top":0 });
		}
	}
	
	function loadContent(href){
		$.ajax({
			url: href
		}).done(function( data ) {
			console.log(data);
			$("#field_inner").load(canonical_url + " #field_inner_load");
			$("#panel_inner").load(canonical_url + " #panel_inner_load");
		});
	}
	
	initWidthHeight();
	$(window).resize(function(){
		initWidthHeight();
	});
	
	var canonical_url = $("link[rel=canonical]").attr("href");
	console.log("canonical", canonical_url);
	
	$("#field").on("click", "a", function(){
		loadContent($(this).attr("href"));
		return false;
	});
	
	$("#panel").on("click", "a.button", function(){
		loadContent($(this).attr("href"));
		return false;
	});
	
	var polling = setInterval(function(){
		var last_action = $("body").attr("data-last-action");
		var baseurl = $("body").attr("data-baseurl");
		$.ajax({
			url: baseurl + "functions/ajax.php",
			data: {
					class : 'Game',
					classID : $("body").attr("data-game-id"),
					method : 'getLastActionID'
			},
			dataType: "json"
		}).done(function(json){
			if(last_action < json.response){
				$("body").attr("data-last-action", json.response)
				console.log("last_action", last_action, "response", json.response);
				loadContent(canonical_url);
			}
		});
	}, 1000);
	
});