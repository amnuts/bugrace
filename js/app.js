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

    $(document).on('click', '#distance-toggler > a', function(e){
        e.preventDefault();
        $('#distance-toggler > a').removeClass('selected');
        $(this).addClass('selected');
        window.location.hash = $(this).prop('hash');
        $(document).trigger('changeDistance', [$(this).prop('hash')]);
    });

    if (window.location.hash) {
        $('#distance-toggler > a[href="' + window.location.hash + '"]').trigger('click');
    }
});