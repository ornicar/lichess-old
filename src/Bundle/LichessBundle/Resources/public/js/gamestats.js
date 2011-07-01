google.setOnLoadCallback(function() {
    $('div.move-time-distribution').each(function() {
        var data = google.elemToData(this);
        var chart = new google.visualization.PieChart(this);
        chart.draw(data, {
            width: 734, 
            height: 400, 
            title: $(this).data('title'),
            chartArea:{left:"0%",top:"0%",width:"100%",height:"100%"},
        });
    });

    $('div.move-time').each(function() {
        var data = google.elemToData(this);
        var chart = new google.visualization.AreaChart(this);
        chart.draw(data, {width: 734,
            height: 400,
            title: $(this).data('title'),
            chartArea:{left:"10%",top:"3%",width:"90%",height:"80%"},
        });
    });
});
