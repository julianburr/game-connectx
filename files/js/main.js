$(document).ready(function(){
	
	var player_id = $("body").attr("data-player-id");
	var baseurl = $("body").attr("data-baseurl");
	var canonical_url = $("link[rel=canonical]").attr("href");
	
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
		clearInterval(polling);
		$.ajax({
			url: href
		}).done(function(data){
			$("#field_inner").load(canonical_url + " #field_inner_load");
			$("#panel_inner").load(canonical_url + " #panel_inner_load");
			$.ajax({
				url: baseurl + "functions/ajax.php",
				data: {
					'class' : 'Game',
					'classID' : $("body").attr("data-game-id"),
					'method' : 'getLastActionID'
				},
				dataType: "json"
			}).done(function(json){
				if(json.response > 0){
					$("body").attr("data-last-action", json.response);
				}
				polling = setInterval(function(){
					pollActions();
				}, 2000);
			});
		});
	}
	
	function pollActions(){
		var last_action = $("body").attr("data-last-action");
		$.ajax({
			url: baseurl + "functions/ajax.php",
			data: {
					'class' : 'Game',
					'classID' : $("body").attr("data-game-id"),
					'method' : 'getActions',
					'args[]' : last_action
			},
			dataType: "json"
		}).done(function(json){
			if(json.actioncnt > 0){
				console.log("ACTIONS FOUND", json.actioncnt);
				console.log(json);
				$.when( doActions(json) ).done(function(){
					loadContent(canonical_url);
				});
			}
			
		});	
	}
	
	function doActions(obj){
		for(var i=0; i<obj.actioncnt; i++){
			var action = obj.actions[i].action_name;
			switch(action){
				case "setStone":
					if(obj.actions[i].action_args.length = 2){
						var action_row = obj.actions[i].action_args[0];
						var action_player = obj.actions[i].action_args[1];
						console.log("ARGS", obj.actions[i].action_args);
						var link_obj = $("#field ul.row_" + action_row).find("li:first").find("span.stone_noplayer");
						console.log(link_obj.length);
						if(link_obj.length > 0){
							action_setStone(link_obj, action_player);
						}
					}
					break;
				default: break;
			}
		}	
	}
	
	function action_setStone(clicked, player){
		console.log("HERE COMES AN ANIMATION");
		var findme = "a";
		if(clicked.hasClass("stone_noplayer")){
			findme = "span.stone_noplayer";
		}
		var lastfield = clicked.closest("ul").find("li:last");
		var lastlink = lastfield.find(findme);
		while(lastlink.length == 0){
			lastfield = lastfield.prev("li");
			lastlink = lastfield.find(findme);
		}
		console.log("LAST FIELD", lastfield, lastlink);
		var top = lastfield.height() / 2;
		var left = lastfield.width()/2;
		lastfield.append("<span class='animated_stone stone stone_player stone_player_" + player + "' style='opacity:0; position:absolute; width:100%; height:100%; z-index:100; top:" + top + "px; left:" + top + "px; width:1px; height:1px;'>" + player + "</span>");
		$(".animated_stone").animate({ 
			"opacity":1,
			"width":"100%",
			"height":"100%",
			"left":0,
			"top":0
		}, 500, function(){
			$(this).css({ "position":"relative" });
			lastfield.find(findme).remove();
		});
	}
	
	initWidthHeight();
	$(window).resize(function(){
		initWidthHeight();
	});
	
	$("#field").on("click", "a", function(){
		var clicked = $(this);
		$.when( action_setStone(clicked, player_id) ).done(function(){
			loadContent(clicked.attr("href"));
		});
		return false;
	});
	
	$("#panel").on("click", "a.button", function(){
		loadContent($(this).attr("href"));
		return false;
	});
	
	var polling = setInterval(function(){
		pollActions();
	}, 2000);

});