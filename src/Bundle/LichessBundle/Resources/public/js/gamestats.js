google.setOnLoadCallback(function() {
    $('div.move-time-distribution').each(function() {
        var data = google.elemToData(this);
        var chart = new google.visualization.PieChart(this);
        chart.draw(data, {width: 734, height: 400, title: $(this).data('title')});
    });

    $('div.move-time').each(function() {
        var data = google.elemToData(this);
        var chart = new google.visualization.AreaChart(this);
        chart.draw(data, {width: 734, height: 400, title: $(this).data('title'),
            hAxis: {title: 'Move', titleTextStyle: {color: '#FF0000'}}
        });
    });
});
