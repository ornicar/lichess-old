google.setOnLoadCallback(function() {

    $('div.elo_history').each(function() {
        var data = google.elemToData(this);
        var chart = new google.visualization.AreaChart(this);
        chart.draw(data, {
            width: 400, 
            height: 350, 
            axisTitlePosition: 'none',
            chartArea:{left:"10%",top:"3%",width:"90%",height:"80%"},
            title: $(this).attr('title'),
            titlePosition: 'in'
        });
    });

    $('div.win_stats').each(function() {
        var data = google.elemToData(this);
        var chart = new google.visualization.PieChart(this);
        chart.draw(data, {
            width: 347, 
            height: 200, 
            chartArea:{left:"0%",top:"0%",width:"100%",height:"100%"},
            is3D: true,
        });
    });
});
