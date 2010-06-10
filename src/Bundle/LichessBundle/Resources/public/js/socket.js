lichess_socket = {
    time: lichess_data.time,
    connect: function(url, callback)
    {
        $.ajax({
            dataType:   'json',
            url:        url,
            success:    function(data) {
                if(data.time > lichess_socket.time) {
                    lichess_socket.time = data.time;
                    callback(data);
                }
                else {
                    callback(false);
                }
            },
            cache:      false,
            error:      function(XMLHttpRequest, textStatus, errorThrown) {
                location.href=location.href;
                //console && console.debug(XMLHttpRequest, textStatus, errorThrown);
            }
        });
    }
}
