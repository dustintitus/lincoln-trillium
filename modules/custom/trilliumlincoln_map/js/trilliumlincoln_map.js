(function ($, Drupal, drupalSettings, window, document, undefined) {

var map, infoWindow, directionsServicem, timedirections, marker;

function InitMap() {
  var buckeye_pos = {};
  var current_pos = {};
  buckeye_pos.lat = +drupalSettings.trilliumlincoln_map.coords.lat;
  buckeye_pos.lng = +drupalSettings.trilliumlincoln_map.coords.lng;
  var mapOptions = {
    zoom: 14,
    mapTypeControl: false,
    panControl: false,
    rotateControl: false,
    overviewMapControl: false,
    streetViewControl: false,
    scaleControl: false,
    scrollwheel: false,
    panControl: false,
    zoomControl: true,
    center: new google.maps.LatLng(buckeye_pos),
    mapTypeId: google.maps.MapTypeId.ROADMAP,
    styles : [
    {
        "featureType": "water",
        "elementType": "geometry",
        "stylers": [
            {
                "color": "#e9e9e9"
            },
            {
                "lightness": 17
            }
        ]
    },
    {
        "featureType": "landscape",
        "elementType": "geometry",
        "stylers": [
            {
                "color": "#f5f5f5"
            },
            {
                "lightness": 20
            }
        ]
    },
    {
        "featureType": "road.highway",
        "elementType": "geometry.fill",
        "stylers": [
            {
                "color": "#ffffff"
            },
            {
                "lightness": 17
            }
        ]
    },
    {
        "featureType": "road.highway",
        "elementType": "geometry.stroke",
        "stylers": [
            {
                "color": "#ffffff"
            },
            {
                "lightness": 29
            },
            {
                "weight": 0.2
            }
        ]
    },
    {
        "featureType": "road.arterial",
        "elementType": "geometry",
        "stylers": [
            {
                "color": "#ffffff"
            },
            {
                "lightness": 18
            }
        ]
    },
    {
        "featureType": "road.local",
        "elementType": "geometry",
        "stylers": [
            {
                "color": "#ffffff"
            },
            {
                "lightness": 16
            }
        ]
    },
    {
        "featureType": "poi",
        "elementType": "geometry",
        "stylers": [
            {
                "color": "#f5f5f5"
            },
            {
                "lightness": 21
            }
        ]
    },
    {
        "featureType": "poi.park",
        "elementType": "geometry",
        "stylers": [
            {
                "color": "#dedede"
            },
            {
                "lightness": 21
            }
        ]
    },
    {
        "elementType": "labels.text.stroke",
        "stylers": [
            {
                "visibility": "on"
            },
            {
                "color": "#ffffff"
            },
            {
                "lightness": 16
            }
        ]
    },
    {
        "elementType": "labels.text.fill",
        "stylers": [
            {
                "saturation": 36
            },
            {
                "color": "#333333"
            },
            {
                "lightness": 40
            }
        ]
    },
    {
        "elementType": "labels.icon",
        "stylers": [
            {
                "visibility": "off"
            }
        ]
    },
    {
        "featureType": "transit",
        "elementType": "geometry",
        "stylers": [
            {
                "color": "#f2f2f2"
            },
            {
                "lightness": 19
            }
        ]
    },
    {
        "featureType": "administrative",
        "elementType": "geometry.fill",
        "stylers": [
            {
                "color": "#fefefe"
            },
            {
                "lightness": 20
            }
        ]
    },
    {
        "featureType": "administrative",
        "elementType": "geometry.stroke",
        "stylers": [
            {
                "color": "#fefefe"
            },
            {
                "lightness": 17
            },
            {
                "weight": 1.2
            }
        ]
    }
]
  };

  map = new google.maps.Map(document.getElementById('map'),mapOptions);

  image = drupalSettings.path.baseUrl + drupalSettings.trilliumlincoln_map.path + '/img/pic.png';

  marker = new google.maps.Marker({
      position: new google.maps.LatLng(buckeye_pos.lat,buckeye_pos.lng),
      map: map,
      icon: image,
  });



   var contentString = '<div id="info-map">'+
      '<div class="address">'+
      '<p><b>4589 Industrial Pkwy</b><br>' +
      'Alliston, ON L9R 1W1.</p>'+
      '</div>'+
      '</div>';


  infoWindow = new google.maps.InfoWindow({
    content: contentString
  });

  google.maps.event.addListener(marker, 'click', function() {
    infoWindow.open(map,marker);
  });


  google.maps.event.addListenerOnce(map, 'idle', function(){
    setTimeout(function(){
      infoWindow.open(map,marker);
    },500);
  });
  

  /*if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function(position) {
      current_pos.lat = position.coords.latitude;
      current_pos.lng = position.coords.longitude;
      getDrivingDirection(current_pos,buckeye_pos);
    }, function() {
      handleLocationError(true);
    });
  } else {
    // Browser doesn't support Geolocation
    handleLocationError(false);
  }*/

  google.maps.event.addDomListener(window, "resize", function() {
      var center = map.getCenter();
      google.maps.event.trigger(map, "resize");
      map.setCenter(center);
  });


}

function getDrivingDirection(current_pos,buckeye_pos){
  //current_pos.lat = 45.406312;
  //current_pos.lng = -75.710231;
  directionsService = new google.maps.DirectionsService;
  directionsService.route({
    origin: current_pos,
    destination: buckeye_pos,
    travelMode: 'DRIVING'
  }, function(result, status) {
    if (status == 'OK') {
      timedirections = secondsToTime(result.routes[0].legs[0].duration.value);
      timestring = timedirections.h + ':' + timedirections.m + 'hrs';
      var contentString = '<div id="info-map" class="directions">'+
      '<div class="time">'+
      timestring +
      '</div>'+
      '<div class="address">'+
      '<p><b>3396 County Road 36</b>' +
      'Bobcaygeon Ontario K0M 1A0.</p>'+
      '</div>'+
      '</div>';

      infoWindow = new google.maps.InfoWindow({
        content: contentString
      });
    }
  });
}

function handleLocationError(browserHasGeolocation, infoWindow, pos) {
  if (browserHasGeolocation) {
    alert('Error: The Geolocation service failed.');
  } else {
    alert('Error: Your browser doesn\'t support geolocation.');
  }

}


function secondsToTime(secs)
{
    secs = Math.round(secs);
    var hours = Math.floor(secs / (60 * 60));

    var divisor_for_minutes = secs % (60 * 60);
    var minutes = Math.floor(divisor_for_minutes / 60);

    var divisor_for_seconds = divisor_for_minutes % 60;
    var seconds = Math.ceil(divisor_for_seconds);

    var obj = {
        "h": hours,
        "m": minutes,
        "s": seconds
    };
    return obj;
}

google.maps.event.addDomListener(window, 'load', InitMap);

})(jQuery, Drupal, drupalSettings, this, this.document);
