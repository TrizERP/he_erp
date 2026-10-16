@include('includes.headcss')
<link rel="stylesheet" href="../../../plugins/bower_components/dropify/dist/css/dropify.min.css">
@include('includes.header')
@include('includes.sideNavigation')

<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">Graphical Attendance</h4>
            </div>
        </div>
        <style>
            .gchart-card{background:#fff;border-radius:8px;padding:12px;margin-bottom:24px;box-shadow:0 1px 4px rgba(0,0,0,.08)}
            .gchart{width:100%;height:480px}
        </style>
        <div class="row">
            <div class="col-md-6 col-sm-12">
                <div class="gchart-card"><div id="container" class="gchart"></div></div>
            </div>
            <div class="col-md-6 col-sm-12">
                <div class="gchart-card"><div id="userChart" class="gchart"></div></div>
            </div>
            <div class="col-md-6 col-sm-12">
                <div class="gchart-card"><div id="studentChart" class="gchart"></div></div>
            </div>
            <div class="col-md-6 col-sm-12">
                <div class="gchart-card"><div id="feesChart" class="gchart"></div></div>
            </div>
        </div>
    </div>
</div>
@include('includes.footerJs')
<script src="https://code.highcharts.com/highcharts.js"></script>
{{-- <script src="https://code.highcharts.com/highcharts-3d.js"></script> --}}
{{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/highcharts/7.2.0/highcharts.js"></script> --}}
<script src="https://code.highcharts.com/modules/sunburst.js"></script>
<script src="https://code.highcharts.com/modules/treemap.js"></script>
<script src="https://code.highcharts.com/modules/drilldown.js"></script>
{{-- <script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script> --}}
<script>
    var data = <?php echo $data['chartData']; ?>;
    console.log(data);
// Splice in transparent for the center circle
Highcharts.getOptions().colors.splice(0, 0, 'transparent');


Highcharts.chart('container', {

    chart: {
        height: 480
    },

    title: {
        text: 'Attendance Chart'
    },
    subtitle: {
        text: 'All Data '
    },
    series: [{
        type: "sunburst",
        data: data,
        allowDrillToNode: true,
        cursor: 'pointer',
        dataLabels: {
            format: '{point.name}',
            filter: {
                property: 'innerArcLength',
                operator: '>',
                value: 16
            }
        },
        levels: [{
            level: 1,
            levelIsConstant: false,
            dataLabels: {
                filter: {
                    property: 'outerArcLength',
                    operator: '>',
                    value: 64
                }
            }
        }, {
            level: 2,
            colorByPoint: true
        },
        {
            level: 3,
            colorVariation: {
                key: 'brightness',
                to: -0.5
            }
        }, {
            level: 4,
            colorVariation: {
                key: 'brightness',
                to: 0.5
            }
        }]

    }],
    tooltip: {
        headerFormat: "",
        pointFormat: 'The Count of <b>{point.name}</b> is <b>{point.value}</b>'
    },credits: {
        enabled: false
    },

});
</script>
<script>
    var userData = @json($data['userChart']);
    var studentData = @json($data['studentChart']);
    var feesData = @json($data['feesChart']);

    // Total Users - donut by department, click a slice to list its users; click a user to open edit
    Highcharts.chart('userChart', {
        chart: {type: 'pie', height: 480},
        title: {text: 'Total Users'},
        subtitle: {text: 'Department wise (click a slice to drill down)'},
        tooltip: {pointFormat: '<b>{point.y}</b> users ({point.percentage:.1f}%)'},
        plotOptions: {
            pie: {
                innerSize: '55%',
                cursor: 'pointer',
                dataLabels: {enabled: true, format: '{point.name}: {point.y}'}
            }
        },
        series: [{name: 'Users', colorByPoint: true, data: userData.main}],
        drilldown: {
            series: userData.drill.map(function (d) {
                if (!d.users) { return d; }   // department level: roles
                return Highcharts.merge(d, {   // role level: individual users
                    type: 'pie',
                    tooltip: {pointFormat: '{point.role}<br><i>Click to edit user</i>'},
                    dataLabels: {enabled: true, format: '{point.name}'},
                    point: {events: {click: function () {
                        if (this.options.url) { window.location.href = this.options.url; }
                    }}}
                });
            })
        },
        credits: {enabled: false}
    });

    // Drilldown series builder: middle level = treemap tiles, leaf level (students) = treemap, click opens profile
    // treemap sizes tiles by `value`, so copy `y` into it
    function withValue(points) {
        return points.map(function (p) { return Highcharts.merge(p, {value: p.y}); });
    }

    function levelSeries(d, tip, amountFmt) {
        d = Highcharts.merge(d, {data: withValue(d.data)});
        if (!d.leaf) {
            return Highcharts.merge(d, {type: 'treemap', layoutAlgorithm: 'squarified', colorByPoint: true,
                dataLabels: {enabled: true, format: amountFmt, style: {textOutline: 'none'}}});
        }
        return Highcharts.merge(d, {
            type: 'treemap', layoutAlgorithm: 'squarified', colorByPoint: true, cursor: 'pointer',
            dataLabels: {enabled: true, format: '{point.name}', style: {textOutline: 'none', fontSize: '11px'}},
            tooltip: {pointFormat: tip},
            point: {events: {click: function () {
                if (this.options.url) { window.location.href = this.options.url; }
            }}}
        });
    }

    // Total Students - treemap: standard -> division -> student
    Highcharts.chart('studentChart', {
        chart: {height: 480},
        title: {text: 'Total Students'},
        subtitle: {text: 'Standard wise (click a tile to drill down)'},
        legend: {enabled: false},
        tooltip: {pointFormat: '<b>{point.name}</b>: {point.y} students'},
        series: [{
            type: 'treemap', layoutAlgorithm: 'squarified', colorByPoint: true, cursor: 'pointer',
            name: 'Students', data: withValue(studentData.main),
            dataLabels: {enabled: true, format: '{point.name}<br>{point.y}', style: {textOutline: 'none', fontSize: '13px'}}
        }],
        drilldown: {
            series: studentData.drill.map(function (d) {
                return levelSeries(d, '<b>{point.name}</b><br><i>Click to open profile</i>', '{point.name}<br>{point.y}');
            })
        },
        credits: {enabled: false}
    });

    // Fees Collected - rounded gradient columns: month -> standard -> student (treemap sized by amount)
    Highcharts.chart('feesChart', {
        chart: {type: 'column', height: 480},
        title: {text: 'Fees Collected'},
        subtitle: {text: 'Month wise (click a column to drill down)'},
        xAxis: {type: 'category', labels: {rotation: -45}},
        yAxis: {min: 0, title: {text: 'Amount'}},
        legend: {enabled: false},
        tooltip: {pointFormat: 'Collected: <b>{point.y:,.2f}</b>'},
        plotOptions: {column: {
            borderRadius: 6, borderWidth: 0, cursor: 'pointer', pointPadding: 0.05, groupPadding: 0.05,
            color: {linearGradient: {x1: 0, y1: 0, x2: 0, y2: 1}, stops: [[0, '#ff8a65'], [1, '#d81b60']]}
        }},
        series: [{name: 'Fees', data: feesData.main}],
        drilldown: {
            series: feesData.drill.map(function (d) {
                return levelSeries(d, '<b>{point.name}</b><br>Paid: {point.y:,.2f}<br><i>Click to open profile</i>',
                    '{point.name}<br>{point.y:,.0f}');
            })
        },
        credits: {enabled: false}
    });
</script>
@include('includes.footer')