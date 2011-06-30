google.load("visualization", "1", {packages:["corechart"]});

google.setOnLoadCallback(function() {

    function elemToData(elem) {
        var data = new google.visualization.DataTable();
        $.each($(elem).data('columns'), function() {
            data.addColumn(this[0], this[1]);
        });
        data.addRows($(elem).data('rows'));

        return data;
    }

    $('div.move-time-distribution').each(function() {
        var data = elemToData(this);
        var chart = new google.visualization.PieChart(this);
        chart.draw(data, {width: 734, height: 400, title: $(this).data('title')});
    });

    $('div.move-time').each(function() {
        var data = elemToData(this);
        var chart = new google.visualization.AreaChart(this);
        chart.draw(data, {width: 734, height: 400, title: $(this).data('title'),
            hAxis: {title: 'Move', titleTextStyle: {color: '#FF0000'}}
        });
    });
});
