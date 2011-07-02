google.setOnLoadCallback(function() {

    $('div.move-time').each(function() {
        var data = google.elemToData(this);
        var chart = new google.visualization.AreaChart(this);
        chart.draw(data, {
            width: 747,
            height: 400,
            title: $(this).data('title'),
            chartArea:{left:"3%",top:"5%",width:"80%",height:"90%"},
        });
    });

    $('div.move-time-distribution').each(function() {
        var data = google.elemToData(this);
        var chart = new google.visualization.PieChart(this);
        chart.draw(data, {
            width: 747, 
            height: 300, 
            title: $(this).data('title'),
            chartArea:{left:"0%",top:"8%",width:"100%",height:"92%"},
            is3D: false
        });
    });
});
