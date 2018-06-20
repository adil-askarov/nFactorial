$(document).ready(function() {
	var container = $(".map-wrapper");
	var bottomdiv = $("div.search-footer-block");
	var cont_offset = container.offset().top;
	var cont_bottom = bottomdiv.offset().top;
	$(window).scroll(function() {
		var windowTop = $(window).scrollTop();
		if((windowTop > cont_offset)) {
			if((windowTop+$(container).height()) < cont_bottom) {
				$(container).css("position","fixed").css("top","0px").css("padding-top","10px").css("bottom","");
			} else {
				$(container).css("position","absolute").css("bottom","0px").css("top","");
			}
		} else{
			$(container).css("position","relative").css("padding-top","0px");
		}
	});
	$(document).on("mouseleave", ".biz-hovercard.biz", function() {
		$(this).hide();
		return false;
	});
	loadScript();
});

var marker;
var map;
var overlay;
var center_latlng;

function loadScript() {
	var script = document.createElement("script");
	script.type = "text/javascript";
	script.src = "https://maps.googleapis.com/maps/api/js?callback=initialize&key=AIzaSyCRwhHOYLVELGcoONPB0AO7_AdozS3pJgw";
	document.body.appendChild(script);
	
	script = document.createElement("script");
	script.type = "text/javascript";
	script.src = root_path+"js/search_infobubble.js";
	document.body.appendChild(script);
}
function SetCenter() {
	map.setCenter(center_latlng);
}
function initialize() {
	center_latlng = new google.maps.LatLng(map_lat, map_lng);
	if(map_lat) {
		var mapOptions = {zoom: zoom, mapTypeControl: false, mapTypeId: google.maps.MapTypeId.ROADMAP, center: center_latlng, zoomControl: true, zoomControlOptions: {style: google.maps.ZoomControlStyle.SMALL}};
		map = new google.maps.Map(document.getElementById("map-container"), mapOptions);
	}
	overlay = new google.maps.OverlayView();
	overlay.draw = function() {};
	overlay.setMap(map);

	div_biz_detail = $('#adv_result');
	$.each(div_biz_detail, function(ind, val) {
		var lat = $(val).attr("lat");
		var lng = $(val).attr("lng");
		var myLatLng = new google.maps.LatLng(lat, lng);
		if(lat!=='' && lng!=='') {
			CreateMarker(map, lat, lng, val, "adv");
		}
	});
	
	div_biz_detail = $('li.regular-search-result');
	$.each(div_biz_detail, function(ind, val) {
		var lat = $(val).attr("lat");
		var lng = $(val).attr("lng");
		var myLatLng = new google.maps.LatLng(lat, lng);
		i++;
		if(lat!=='' && lng!=='') {
			CreateMarker(map, lat, lng, val, i);
		}
	});

}
var marker_img;
var marker_img_position;
function FindMarkerImage(i) {
	if(typeof(i) == 'string') {
		if(i=="adv") {
			marker_img = "adv_marker.png";
			marker_img_position = 0;
		}
	} else
	if(i <= 500) {
		marker_img = (Math.ceil(i/10)*10).toString()+".png";
		i = i - Math.floor((i-1)/10)*10;
		marker_img_position = (i-1)*40;
	}
}

function CreateMarker(map, map_lat_v, map_lng_v, value_e, i) {
	FindMarkerImage(i);
	var yMarker = new google.maps.MarkerImage(root_path+"styles/images/markers/"+marker_img,
				new google.maps.Size(30,39),
				new google.maps.Point(30, marker_img_position),
				new google.maps.Point(15,39));
	var image = new google.maps.MarkerImage(root_path+"styles/images/markers/"+marker_img,
				new google.maps.Size(30,39),
				new google.maps.Point(0, marker_img_position),
				new google.maps.Point(15,39));
	var shadow = new google.maps.MarkerImage(root_path+"styles/images/marker_shadow.png",
				new google.maps.Size(38, 29),
				new google.maps.Point(0,0),
				new google.maps.Point(17, 29));
	var shape = {
				coord: [1, 1, 1, 39, 30, 39, 30 , 1],
				type: 'poly'
				};
	var myLatLng = new google.maps.LatLng(map_lat_v, map_lng_v);
	var curr_marker = new google.maps.Marker({
				position: myLatLng,
				map: map,
				/*shadow: shadow,*/
				icon: image,
				shape: shape
				});
	google.maps.event.addListener(curr_marker, 'click', function() {
				openInfoWindow_new(this, i, image);
				curr_marker.setOptions({icon: yMarker});
				});
	google.maps.event.addListener(curr_marker, 'mouseover', function(e) {
				openInfoWindow_new(this, i, image);
				curr_marker.setOptions({icon: yMarker});
				});
	google.maps.event.addListener(curr_marker, 'mouseout', function() {
				if(!$(".biz-hovercard.biz").is(':hover')) {
					closeInfoWindow(curr_marker, image);
				}
				curr_marker.setOptions({icon: image});
				});
	google.maps.event.addListener(map, 'click', function() {
				closeInfoWindow(curr_marker, image);
				});
}

function closeInfoWindow(marker, newimage) {
	$(".biz-hovercard.biz").hide();
};

function openInfoWindow_new(umarker, i, newicon) {
	var projection = overlay.getProjection(); 
	var pixel = projection.fromLatLngToContainerPixel(umarker.getPosition());
	var biz_div = $('div[data-key='+i+']');
	var bubble_div = $(".biz-hovercard.biz");
	
	$(bubble_div).find(".media-title").html($(biz_div).find(".biz-name").clone());
	$(bubble_div).find(".biz-rating").html($(biz_div).find(".biz-rating").html());
	$(bubble_div).find(".price-category").html($(biz_div).find(".price-category").html());
	$(bubble_div).find("address").html($(biz_div).find("address").html());
	$(bubble_div).find(".media-avatar").html($(biz_div).find(".media-avatar").html());
	
	$(bubble_div).css("top", ($("#map-container").offset().top+pixel.y-2)+"px").css("left", ($("#map-container").offset().left+pixel.x-328)+"px").show();
}