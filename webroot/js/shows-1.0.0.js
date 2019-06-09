/**
 * @author Daniel Jones
 */
var isMobile = {
    Windows: function() {
        return /IEMobile/i.test(navigator.userAgent);
    },
    Android: function() {
        return /Android/i.test(navigator.userAgent);
    },
    BlackBerry: function() {
        return /BlackBerry/i.test(navigator.userAgent);
    },
    iOS: function() {
        return /iPhone|iPad|iPod/i.test(navigator.userAgent);
    },
    any: function() {
        return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Windows());
    }
};

$(document).ready(function() {
	$( window ).resize(function() {
		$('.ui-contextmenu').hide();
		if ($('#edit_season').is(":focus")) {
			$('#edit_season').focus();
			window.scroll(0, findPos(document.getElementByClass(".ui-dialog-titlebar")));
		}
		if ($('#edit_episode').is(":focus")) {
			$('#edit_episode').focus();
			window.scroll(0, findPos(document.getElementByClass(".ui-dialog-titlebar")));
		}
	});
	
	//Deluge Navigation
	$('a[href="http://djjtor.duckdns.org:8083"]').click(function() {
		loading();
		var url = $(this).attr('href');
		$.ajax({
			type: "POST",
			dataType: 'json',
			url: "/tveditor/shows/startDeluge",
			success : function(data) {
				if (data.text == "started")
				{
					$('#loading').dialog( "close" );
					var win = window.open(url, '_blank');
					if(win) {
						win.focus()
					}
					else {
						genericAlert("<p><span class='ui-icon ui-icon-alert red tveditors-icon'></span>Please allow Pop-Ups for this Site!.</p>", "Error!", "dialog-error");
					}
				}
				else if (data.text == "fail")
				{
					$('#loading').dialog( "close" );
					genericAlert("<p><span class='ui-icon ui-icon-alert red tveditors-icon'></span>Could not start deluge. Please Contact Your Server Administrator.</p>", "Error!", "dialog-error");
				}
				else if (data.text == "Unauthorized")
				{
					genericAlert("<p><span class='ui-icon ui-icon-alert red tveditors-icon'></span>You are not authorized to perform this action. Please contact your Server Administrator if you have any questions.</p>", "Unauthorized!", "dialog-error");
					$('#loading').dialog( "close" );
				}
				else {
					genericAlert("<p><span class='ui-icon ui-icon-alert red tveditors-icon'></span>Error Processing Your Request. Please Contact Your Server Administrator.</p>", "Error!", "dialog-error");
					$('#loading').dialog( "close" );
				}
			},
			error: function() {
				genericAlert("<p><span class='ui-icon ui-icon-alert red tveditors-icon'></span>Error Processing Your Request. Please Contact Your Server Administrator.</p>", "Error!", "dialog-error");
				$('#loading').dialog( "close" );
			}
		});
		
		return false;
		
	});
	
	$('#btn_remove').click(function() {
		moveShows('active', 'removed');
	});
	
	$('#btn_add').click(function() {
		moveShows('removed', 'active');
	});
	
	$('#submit_shows').click(function() {
		loading();
		var activeShows = [];
		var removedShows = [];
		
		$('.active > .show > span').each(function() {
			activeShows.push($.trim($(this).text()));
		});
		
		$('.removed > .show > span').each(function() {
			removedShows.push($.trim($(this).text()));
		});

		$.ajax({
			type: "POST",
			data: {'Active_Shows': JSON.stringify(activeShows), 'Removed_Shows': JSON.stringify(removedShows)},
			dataType: 'json',
			url: "/tveditor/shows/mainSubmit",
			success : function(data) {
				if (data.text == "Success")
				{
					genericAlert("<p><span class='ui-icon ui-icon-circle-check green tveditors-icon'></span>Sucessfully Updated Download Lists!</p>", "Success!", "dialog-success");
					$('#loading').dialog( "close" );
				}
				else if (data.text == "Unauthorized")
				{
					genericAlert("<p><span class='ui-icon ui-icon-alert red tveditors-icon'></span>You are not authorized to perform this action. Please contact your Server Administrator if you have any questions.</p>", "Unauthorized!", "dialog-error");
					$('#loading').dialog( "close" );
				}
				else {
					genericAlert("<p><span class='ui-icon ui-icon-alert red tveditors-icon'></span>Error Processing Your Request. Please Contact Your Server Administrator.</p>", "Error!", "dialog-error");
					$('#loading').dialog( "close" );
				}
			},
			error: function() {
				genericAlert("<p><span class='ui-icon ui-icon-alert red tveditors-icon'></span>Error Processing Your Request. Please Contact Your Server Administrator.</p>", "Error!", "dialog-error");
				$('#loading').dialog( "close" );
			}
		});
	});
		
	$(' #add_new ').click(function() {
		if (!$(' #new-show' ).val())
		{
			genericAlert("<p><span class='ui-icon ui-icon-alert red tveditors-icon'></span>You Must Enter a TV Show!</p>", "Missing Information!", "dialog-error");
		}
		else if ($(' #new-show' ).val() && (!$('#season').val() && $('#episode').val()))
		{
			genericAlert("<p><span class='ui-icon ui-icon-alert red tveditors-icon'></span>You Must Enter a Start Season for this Show!</p>", "Missing Information!", "dialog-error");
		}
		else if ($(' #new-show' ).val() && ($('#season').val() && !$('#episode').val()))
		{
			genericAlert("<p><span class='ui-icon ui-icon-alert red tveditors-icon'></span>You Must Enter a Start Episode for this Show!</p>", "Missing Information!", "dialog-error");
		}
		else if (($('#season').val().length == 1 && $('#season').val()) || ($('#episode').val().length == 1 && $('#episode').val()))
		{
			genericAlert("<p><span class='ui-icon ui-icon-alert red tveditors-icon'></span>Season and Episode Must Contain 2 Numbers!</p>", "Invalid Information!", "dialog-error");
		}
		else if ($('#season').val() == "00" || $('#episode').val() == "00")
		{
			genericAlert("<p><span class='ui-icon ui-icon-alert red tveditors-icon'></span>Season and Episode Cannot Be 00!</p>", "Invalid Information!", "dialog-error");
		}
		else {
			loading();
			$.ajax({
				type: "POST",
				data: $(' #new_form ').serialize(),
				dataType: 'json',
				url: "/tveditor/shows/TvDbLookup",
				success : function(data) {
					if (data.code == "error")
					{
						genericAlert("<p><span class='ui-icon ui-icon-alert red tveditors-icon'></span>Error Processing Your Request. Please Contact Your Server Administrator.</p>", "Error!", "dialog-error");
						$('#loading').dialog( "close" );
					}
					else if (data.code == "Not Found") {
						genericAlert("<p><span class='ui-icon ui-icon-alert red tveditors-icon'></span>Show Not Found! Please Check Your Spelling!</p>", "Error!", "dialog-error");
						$('#loading').dialog( "close" );
					}
					else if (data.code == "success") {
						var mobileNote = "";
						if (isMobile.any()) {
							mobileNote = "<div id='mobileNote'>NOTE: Press and Hold Down for Additional Show Info.</div>";
						}
						else {
							mobileNote = "<div id='mobileNote'>NOTE: Hover for Additional Show Info.</div>";
						}
						tvDbresults(mobileNote + data.html);
					}
					else
					{
						genericAlert("<p><span class='ui-icon ui-icon-alert red tveditors-icon'></span>Error Processing Your Request. Please Contact Your Server Administrator.</p>", "Error!", "dialog-error");
						$('#loading').dialog( "close" );
					}
				},
				error: function() {
					genericAlert("<p><span class='ui-icon ui-icon-alert red tveditors-icon'></span>Error Processing Your Request. Please Contact Your Server Administrator.</p>", "Error!", "dialog-error");
					$('#loading').dialog( "close" );
				}
			});
		}	
	});
	
	if (user != "read")
	{
		$(".active").contextmenu({
	    	delegate: ".show",
	    	menu: [
	        	{title: "Edit", cmd: "edit", uiIcon: "ui-icon-pencil blue"},
				{title: "Remove", cmd: "remove", uiIcon: "ui-icon-close red"},
	        ],
	    	select: function(event, ui) {
	    		if (ui.cmd == "remove") {
	    			moveSingle(ui.target.closest('div'), "removed");
	    		}
	    		else if (ui.cmd == "edit") {
	    			showEditDialog($.trim(ui.target.text()));
	    		}
	        	
	    	}
		});
		$(".removed").contextmenu({
	    	delegate: ".show",
	    	menu: [
	        	{title: "Edit", cmd: "edit", uiIcon: "ui-icon-pencil blue"},
				{title: "Add", cmd: "add", uiIcon: "ui-icon-plus green"},
				{title: "Delete", cmd: "delete", uiIcon: "ui-icon-close red"},
	        ],
	    	select: function(event, ui) {
	    		if (ui.cmd == "add") {
	    			moveSingle(ui.target.closest('div'), "active");
	    		}
	    		else if (ui.cmd == "edit") {
					showEditDialog($.trim(ui.target.text()));
	    		}
	    		else if (ui.cmd == "delete") {
	    			purgeConfirm($.trim(ui.target.text()));
	    		}
	    	}
		});
	}
	else {
		$(".active").contextmenu({
	    	delegate: ".show",
	    	menu: [
	        	{title: "View", cmd: "edit", uiIcon: "ui-icon-pencil blue"},
	        ],
	    	select: function(event, ui) {
				if (ui.cmd == "edit") {
	    			showEditDialog($.trim(ui.target.text()));
	    		}        	
	    	}
		});
		$(".removed").contextmenu({
	    	delegate: ".show",
	    	menu: [
	        	{title: "View", cmd: "edit", uiIcon: "ui-icon-pencil blue"},
	        ],
	    	select: function(event, ui) {
				if (ui.cmd == "edit") {
					showEditDialog($.trim(ui.target.text()));
	    		}
	    	}
		});
	}
		
	$(document).click(function(e) {
		if (!$(e.target).is("button, input"))
		{
			$(".shows-csv > .selected").addClass("unselected");
			$(".shows-csv > .selected").removeClass("selected");
		}
	});
	
	$('body').on('contextmenu', 'img', function(e){ return false; });
	
		
	$('#loading').on('dialogclose', function(e) {
		$('#loading').hide();
	});
});

var pressTimer;

$(document).on("click", "#tvDbShows > .unselected", function() {
	if (user != "read")
	{
		$( '#tvDbShows > .selected ' ).addClass('unselected');
		$( '#tvDbShows > .selected ' ).removeClass('selected');
		$(this).addClass("selected");
		$(this).removeClass("unselected");	
	}

}).on("focus", ".season-ep", function(e) {
	this.select();
}).on("keypress", ".season-ep", function(e) {
		var length = $(this).val().length;
		var pressed = String.fromCharCode(e.which);
		var regex = new RegExp("^[0-9]+$");
	
	    var key = String.fromCharCode(!e.charCode ? e.which : e.charCode);
	
	    if (!regex.test(key)) {
	        e.preventDefault();
	        return false;
	    }
        // Ensure that it is a number and stop the keypress
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
        else if (length == 2 && this.select()) {
        	e.preventDefault();
        }
        else if (length == 1) {
        	if ($(this).attr('id') == 'season') {
        		$('#episode').focus();
        	}
        	else if ($(this).attr('id') == 'episode') {
        		$(this).val($(this).val() + pressed);
        		$('#add_new').focus();
        	}
        	else if ($(this).attr('id') == 'edit_season') {
        		$('#edit_episode').focus();
        	}
        	else if ($(this).attr('id') == 'edit_episode') {
        		$(this).val($(this).val() + pressed);
        		$('#save_show').focus();
        	}
        }
        
}).on("paste", ".season-ep", function(e) {
	    // access the clipboard using the api
        var pastedData = e.originalEvent.clipboardData.getData('text');
        
        if (pastedData.length > 2 || $.isNumeric(pastedData) === false) {
        	e.preventDefault();
        }
        
}).on("click", "#tvDbShows > .selected", function() {
	$(this).removeClass("selected");	
	$(this).addClass("unselected");
	
}).on("click", ".shows-csv > .unselected", function(e) {
	if (user != "read")
	{
		e.stopPropagation();
		$(this).addClass("selected");
		$(this).removeClass("unselected");
	}
	
}).on("click", ".shows-csv > .selected", function(e) {
	if (user != "read")
	{
		e.stopPropagation();
		$(this).removeClass("selected");	
		$(this).addClass("unselected");
	}
	
}).on("focus", "#edit_season", function() {
	$("body").scrollTop($('#edit_season').offset().top);

}).on("mouseup", "#tvDbShows > .unselected, #tvDbShows > .selected", function() {
	clearTimeout(pressTimer)
  	// Clear timeout
  	return false;
  	
}).on("mousedown", "#tvDbShows > .unselected, #tvDbShows > .selected", function(e) {
	if (isMobile.any())
	{
		var name = $(this).children('#theShow').html();
		var description = $(this).children('.show-description').html();
		
		var network = $(this).children('#theNetwork').html();

  		// Set timeout
  		pressTimer = window.setTimeout(function(e) {mobileInfo(description, name + " - " +  network);return false;},2000)
  		return false; 
  	}
}).on("click", "#expandOverview", function() {
	if($('#edit-overview').is(':visible'))
	{
		$("#expandOverview > span").removeClass("ui-icon ui-icon-squaresmall-minus");
		$("#expandOverview > span").addClass("ui-icon ui-icon-squaresmall-plus");
		$('#edit-overview').slideUp();	
	}
	else {
		$("#expandOverview > span").removeClass("ui-icon ui-icon-squaresmall-plus");
		$("#expandOverview > span").addClass("ui-icon ui-icon-squaresmall-minus");
		$('#edit-overview').slideDown();
	}
}).on("click", "#expand-episode", function() {
	if($('#ep-overview').is(':visible'))
	{
		$("#expand-episode > span").removeClass("ui-icon ui-icon-squaresmall-minus");
		$("#expand-episode > span").addClass("ui-icon ui-icon-squaresmall-plus");
		$('#ep-overview').slideUp();	
	}
	else {
		$("#expand-episode > span").removeClass("ui-icon ui-icon-squaresmall-plus");
		$("#expand-episode > span").addClass("ui-icon ui-icon-squaresmall-minus");
		$('#ep-overview').slideDown();
	}
});
		
function moveShows(removeClass, addClass) {
	$("." + removeClass + " > .selected").each(function () {
		$("." + addClass).append($(this));    	
	});
	sort("." + addClass);
}
function moveSingle($elem, addClass) {
	$("." + addClass).append($elem);
	sort("." + addClass);
}

function sort(selector) {
	var items = $(selector).children("div").sort(function(a, b) {
        var vA = $("span", a).text();
        var vB = $("span", b).text();
        return (vA < vB) ? -1 : (vA > vB) ? 1 : 0;
    });
    
    $(selector).append(items);
}
function genericAlert(html, title, cssClass, reload) {
	$('#message').html(html);
	$('#message').dialog({
		modal: true,
		title: title,
		dialogClass: cssClass,
		width: 400,
		buttons: {
			OK: function() {
				if(reload)
				{
					location.reload(true);
				}
				else
				{
					$(this).dialog( "close" );
				}
			}
		},
		responsive: true,
		clickOut: false
	});
}
function tvDbresults(html) {
	$('#tvDbShows').html(html);
	var title = "Select Your Show: " + $( '#new-show' ).val();
	var buttons = {
			Add: {
				text: "Add Show",
				id: "tvDbAdd",
				click: function(){
					if ( $('.selected').length )
					{
						loading();
						addNewShow();
						$(this).dialog( "close" );
					}
					else {
						genericAlert("<p><span class='ui-icon ui-icon-alert red tveditors-icon'></span>You Must Select a TV Show!</p>", "Missing Information!", "dialog-error");
					}
					
				}
			},
			Cancel: function() {
				$(this).dialog( "close" );
			}
	};
	if (user == "read")
	{
		title = "View Shows: " + $( '#new-show' ).val();
		buttons = {
			Close: function() {
				$(this).dialog( "close" );
			}
		};
	}
	$('#tvDbShows').dialog({
		modal: true,
		title: title,
		dialogClass: "dialog-shows",
		width: 1050,
		height: 1200,
		buttons: buttons,
		responsive: true,
		clickOut: false,
	});
	
	$('#loading').dialog( "close" );
}
function mobileInfo(html, title) {
	$('#mobileInfo').html(html);
	$('#mobileInfo').dialog({
		modal: true,
		title: title,
		dialogClass: "dialog-shows",
		width: 400,
		height: 400,
		buttons: {
			OK: function() {
				$(this).dialog( "close" );
			}
		},
		responsive: true,
	});
}
function addNewShow() {
	var showId = $('#tvDbShows > .selected').attr("id");
	var show = $.trim($('#tvDbShows > .selected').children('#theShow').html());
	var season = $('#season').val();
	var episode = $('#episode').val();
	
	$.ajax({
		type: "POST",
		data: {'show': show, 'season': season, 'episode': episode, 'showId': showId},
		dataType: 'json',
		url: "/tveditor/shows/addShow",
		success : function(data) {
			if (data.result == "Success")
			{
				$('#loading').dialog( "close" );
				genericAlert("<p><span class='ui-icon ui-icon-circle-check green tveditors-icon'></span>" + data.html + "</p>", "Success!", "dialog-success", true);
			}
			else if (data.result == "exists"){
				$('#loading').dialog( "close" );
				genericAlert("<p><span class='ui-icon ui-icon-alert red tveditors-icon'></span>Show Already Exists! Please Edit the  Show Information</p>", "Error!", "dialog-error");
			}
			else if (data.result == "Unauthorized")
			{
				genericAlert("<p><span class='ui-icon ui-icon-alert red tveditors-icon'></span>You are not authorized to perform this action. Please contact your Server Administrator if you have any questions.</p>", "Unauthorized!", "dialog-error");
				$('#loading').dialog( "close" );
			}
			else {
				$('#loading').dialog( "close" );
				genericAlert("<p><span class='ui-icon ui-icon-alert red tveditors-icon'></span>Error Processing Your Request. Please Contact Your Server Administrator. Message:" + data.text + "</p>", "Error!", "dialog-error");
			}
		},
		error: function() {
			$('#loading').dialog( "close" );
			genericAlert("<p><span class='ui-icon ui-icon-alert red tveditors-icon'></span>Error Processing Your Request. Please Contact Your Server Administrator.</p>", "Error!", "dialog-error");
		}
	});
}
function loading() {
	$('#loading').show();
	$('#loading').dialog({
		modal: true,
		closeOnEscape: false,
		width: 400,
		height: 400,
		dialogClass: "dialog-loading",
		responsive: true,
		clickOut: false,
		showCloseButton: false,
		open: function (event, ui) {
			$('#loading').css('overflow', 'hidden');
		}
	});
}
function showEditDialog(show, from, successHtml) {
	loading();
	var buttons = {
		"Save" : { 
					text: "Save",
					id: 'save_show',
					click: function() {
						var season = $("#edit_season").val();
						var episode = $("#edit_episode").val();
						if (!season && episode)
						{
							genericAlert("<p><span class='ui-icon ui-icon-alert red tveditors-icon'></span>You Must Enter a Start Season for" + show + "!</p>", "Missing Information!", "dialog-error");
						}
						else if (season && !episode)
						{
							genericAlert("<p><span class='ui-icon ui-icon-alert red tveditors-icon'></span>You Must Enter a Start Episode for " + show + "!</p>", "Missing Information!", "dialog-error");
						}
						else if (!season && !episode)
						{
							genericAlert("<p><span class='ui-icon ui-icon-alert red tveditors-icon'></span>You Must Enter Start Season and Start Episode for " + show + "!</p>", "Missing Information!", "dialog-error");
						}
						else if ((season.length == 1 && season) || (episode.length == 1 && episode))
						{
							genericAlert("<p><span class='ui-icon ui-icon-alert red tveditors-icon'></span>Season and Episode Must Contain 2 Numbers!</p>", "Invalid Information!", "dialog-error");
						}
						else if (season == "00" || episode == "00")
						{
							genericAlert("<p><span class='ui-icon ui-icon-alert red tveditors-icon'></span>Season and Episode Cannot Be 00!</p>", "Invalid Information!", "dialog-error");
						}
						else {
							updateConfirm(show, season, episode);
						}
					}
		},
		"Delete" : {
					text: "Delete",
					click: function() {
						purgeConfirm(show);
					}
		}
	};
	if (user == "read")
	{
		buttons = {
			Close: function() {
				$(this).dialog( "close" );
			}
		};
	}
	$.ajax({
		type: "POST",
		data: {'show': show},
		dataType: 'json',
		url: "/tveditor/shows/editShow",
		success : function(data) {
			if (data.code == "success")
			{
				$('#loading').dialog( "close" );
				$('#editDiv').html(data.html);
				$('#editDiv').dialog({
					modal: true,
					closeOnEscape: false,
					width: 600,
					height: 600,
					title: data.title,
					dialogClass: "dialog-shows",
					responsive: true,
					clickOut: true,
					buttons: buttons,
					open: function() {
						$('#edit_season').blur();
						$('#editDiv').css("overflow", "auto");
					}
				});
				if (from == "update")
				{
					genericAlert("<p><span class='ui-icon ui-icon-circle-check green tveditors-icon'></span>" + successHtml + "</p>", "Success!", "dialog-success", false);
				}
				$('[role=application] span:first-child').addClass('online');
				$('[role=application] span:nth-child(2)').addClass('offline');
			}
			else
			{
				$('#loading').dialog( "close" );
				genericAlert("<p><span class='ui-icon ui-icon-alert red tveditors-icon'></span>Error Processing Your Request. Please Contact Your Server Administrator." + data.code + "</p>", "Error!", "dialog-error");
			}
		},
		error: function() {
			$('#loading').dialog( "close" );
			genericAlert("<p><span class='ui-icon ui-icon-alert red tveditors-icon'></span>Error Processing Your Request. Please Contact Your Server Administrator.</p>", "Error!", "dialog-error");
		}
	});
}
function purgeConfirm(show) {
	$('#confirmBox').html("<span class='ui-icon ui-icon-alert yellow tveditors-icon'></span>  Are You Sure You Want Completely Delete " + show + "?");
	$('#confirmBox').dialog({
	modal: true,
		closeOnEscape: false,
		width: 400,
		title: "Confirm Delete",
		dialogClass: "dialog-confirm",
		responsive: true,
		clickOut: true,
		buttons: {
			Yes: function() {
				loading();
				$(this).dialog("close");
				purge(show);
			},
			No: function() {
				$(this).dialog("close");
			}
		}
	});
}

function updateConfirm(show, season, episode) {
	$('#confirmBox').html("<span class='ui-icon ui-icon-alert yellow tveditors-icon'></span>  Are You Sure You Want to Update the Start Episode to Season " + season + " Episode " + episode + " for " + show  + "?");
	$('#confirmBox').dialog({
		modal: true,
		closeOnEscape: false,
		width: 400,
		dialogClass: "dialog-confirm",
		title: "Confirm Update",
		responsive: true,
		clickOut: true,
		buttons: {
			Yes: function() {
				loading();
				$(this).dialog("close");
				updateShow(show, season, episode);
			},
			No: function() {
				$(this).dialog("close");
			}
		}
	});
}

function purge(show) {
	$.ajax({
		type: "POST",
		data: {'show': show},
		dataType: 'json',
		url: "/tveditor/shows/deleteShow",
		success : function(data) {
			if (data.result == "Success")
			{
				$('#loading').dialog( "close" );
				genericAlert("<p><span class='ui-icon ui-icon-circle-check green tveditors-icon'></span>" + data.html + "</p>", "Success!", "dialog-success", true);
			}
			else if (data.result == "Unauthorized")
			{
				genericAlert("<p><span class='ui-icon ui-icon-alert red tveditors-icon'></span>You are not authorized to perform this action. Please contact your Server Administrator if you have any questions.</p>", "Unauthorized!", "dialog-error");
				$('#loading').dialog( "close" );
			}
			else {
				$('#loading').dialog( "close" );
				genericAlert("<p><span class='ui-icon ui-icon-alert red tveditors-icon'></span>Error Processing Your Request. Please Contact Your Server Administrator. Message:" + data.result + "</p>", "Error!", "dialog-error");
			}
		},
		error: function() {
			$('#loading').dialog( "close" );
			genericAlert("<p><span class='ui-icon ui-icon-alert red tveditors-icon'></span>Error Processing Your Request. Please Contact Your Server Administrator.</p>", "Error!", "dialog-error");
		}
	});
}
function updateShow(show, season, episode) {
	$.ajax({
		type: "POST",
		data: {'show': show, 'season': season, 'episode': episode},
		dataType: 'json',
		url: "/tveditor/shows/setShowStart",
		success : function(data) {
			if (data.result == "Success")
			{
				showEditDialog(show, "update", data.html);
			}
			else if (data.result == "Unauthorized")
			{
				genericAlert("<p><span class='ui-icon ui-icon-alert red tveditors-icon'></span>You are not authorized to perform this action. Please contact your Server Administrator if you have any questions.</p>", "Unauthorized!", "dialog-error");
				$('#loading').dialog( "close" );
			}
			else {
				$('#loading').dialog( "close" );
				genericAlert("<p><span class='ui-icon ui-icon-alert red tveditors-icon'></span>Error Processing Your Request. Please Contact Your Server Administrator. Message:" + data.result + "</p>", "Error!", "dialog-error");
			}
		},
		error: function() {
			$('#loading').dialog( "close" );
			genericAlert("<p><span class='ui-icon ui-icon-alert red tveditors-icon'></span>Error Processing Your Request. Please Contact Your Server Administrator.</p>", "Error!", "dialog-error");
		}
	});
}
function findPos(obj) {
	var curtop = 0;
	if (obj.offsetParent) {
		do {
			curtop += obj.offsetTop;
		} while (obj = obj.offsetParent);
		return [curtop];
	}
}

