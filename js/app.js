var markers = [];
var bounds;
var map;

$(document).foundation();

function initMap() {
    map = new google.maps.Map(document.getElementById('map'), {
        zoom: 12,
        center: {lat: 52.520, lng: 13.410}
    });
}

function drop(points) {
    clearMarkers();
    bounds = new google.maps.LatLngBounds();
    for (var i = 0; i < points.length; i++) {
        bounds.extend(new google.maps.LatLng(points[i].lat, points[i].lng));
    }
    map.fitBounds(bounds);
    map.panToBounds(bounds);
    for (var i = 0; i < points.length; i++) {
        addMarkerWithTimeout(points[i], i * 200);
    }
}

function addMarkerWithTimeout(position, timeout) {
    window.setTimeout(function() {
        markers.push(new google.maps.Marker({
            position: position,
            map: map,
            animation: google.maps.Animation.DROP
        }));
    }, timeout);
}

function clearMarkers() {
    for (var i = 0; i < markers.length; i++) {
        markers[i].setMap(null);
    }
    markers = [];
}

$(function(){
    $(document).on('changeDistance', function(e, to){
        if (to.charAt(0) == '#') {
            to = to.substring(1);
        }
        $('[data-distance]').each(function(){
            $(this).html($(this).data(to));
        });
    });
    
    $(document).on('changeLocation', function(e, to){
        if (to.charAt(0) == '#') {
            to = to.substring(1);
        }
        $('[data-location]').each(function(){
            $(this).html($(this).data(to));
        });
    });

    $(document).on('click', '#distance-toggler > a', function(e){
        e.preventDefault();
        $('#distance-toggler > a').removeClass('selected');
        $(this).addClass('selected');
        window.location.hash = $(this).prop('hash') + '-' + $('#location-toggler > a').prop('hash').substring(1);
        $(document).trigger('changeDistance', [$(this).prop('hash')]);
    });

    $(document).on('click', '#location-toggler > a', function(e){
        e.preventDefault();
        $('#location-toggler > a').removeClass('selected');
        $(this).addClass('selected');
        window.location.hash = $('#location-toggler > a').prop('hash') + '-' + $(this).prop('hash').substring(1);
        $(document).trigger('changeLocation', [$(this).prop('hash')]);
    });

    $(document).on('click', 'i.show-map', function(e){
        e.preventDefault();
        clearMarkers();
        $('#mapModal').foundation('open');
        initMap();
        drop(mapData[$(this).data('for')]);
    });

    if (window.location.hash) {
        var parts = window.location.hash.split('-', 2);
        $('#distance-toggler > a[href="' + parts[0] + '"]').trigger('click');
        $('#location-toggler > a[href="' + parts[1] + '"]').trigger('click');
    }
});