;(function($){
    $.extend({
        refreshSession: function(option){
            var defaults = {
                url: '',
                time: 120000
            };
            var settings = $.extend(defaults, option);

            return refresh();

            function refresh(){
                setInterval(function(){
                    $.ajax({
                        url: settings.url,
                        type: 'post',
                        data: {},
                        dataType: 'json',
                        success: function(response, textStatus, jqXHR) {},
                        error: function(jqXHR, textStatus, errorThrown) {}
                    });
                }, settings.time);
            }
        }
    });
})(jQuery);