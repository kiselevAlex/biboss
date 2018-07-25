
(function( $ ){

    $.fn.trackCoords = function(param) {

        if (param.url == undefined){
            console.error("url not exist");
            return;
        }

        param.checkInterval = (param.checkInterval != undefined) ? param.checkInterval : 30;
        param.sendInterval = (param.sendInterval  != undefined) ? param.sendInterval : 30000;

        var coords = [];

        var Xinner, Yinner;

        var sender = this;

        $("body").mousemove(function(e){
            pos = sender.offset();
            elem_left = pos.left;
            elem_top = pos.top;
            Xinner = e.pageX - elem_left;
            Yinner = e.pageY - elem_top;
        });

        setInterval(function(){
            if (Xinner != undefined && Yinner != undefined) {
                el = coords.filter(function (el) {
                    return el.x == Xinner && el.y == Yinner;
                });

                if (el.length > 0)
                    el[0].time += param.checkInterval;
                else
                    coords.push({x: Xinner, y: Yinner, time: param.checkInterval});
            }
        }, param.checkInterval);

        setInterval(function(){
            $.post(param.url, {coords: coords}).fail(function(e) {
                    console.error(e);
                }
            )
            coords = [];
        }, param.sendInterval);
    };
})( jQuery );