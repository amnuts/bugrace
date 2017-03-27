$(document).foundation()

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

    if (window.location.hash) {
        var parts = window.location.hash.split('-', 2);
        $('#distance-toggler > a[href="' + parts[0] + '"]').trigger('click');
        $('#location-toggler > a[href="' + parts[1] + '"]').trigger('click');
    }
});