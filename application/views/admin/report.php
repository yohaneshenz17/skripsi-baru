<?php $this->app->extend('template/admin') ?>

<?php $this->app->setVar('title', "Report") ?>

<?php $this->app->section() ?>
<style>
    #chartdiv {
        width: 100%;
        height: 500px;
    }
</style>
<div class="card">
    <div class="card-header">
        <div class="card-title">Data Report</div>
    </div>
    <div class="card-body">
        <div id="chartdiv"></div>
    </div>
</div>
<?php $this->app->endSection('content') ?>

<?php $this->app->section() ?>
<script src="https://cdn.amcharts.com/lib/4/core.js"></script>
<script src="https://cdn.amcharts.com/lib/4/charts.js"></script>
<script src="https://cdn.amcharts.com/lib/4/themes/frozen.js"></script>
<script src="https://cdn.amcharts.com/lib/4/themes/animated.js"></script>
<script>
    $(document).ready(function() {

        // var barChartCanvas = $('#report').get(0).getContext('2d');

        const data = [];
        call('api/proposal_mahasiswa').done(function(res) {
            data.push(res.data.length)

            call('api/seminar').done(function(res) {
                data.push(res.data.length)

                call('api/skripsi/admin_index').done(function(res) {
                    data.push(res.data.length)

                    // Chart
                    am4core.ready(function() {

                        // Themes begin
                        am4core.useTheme(am4themes_frozen);
                        am4core.useTheme(am4themes_animated);
                        // Themes end

                        // Create chart instance
                        var chart = am4core.create("chartdiv", am4charts.XYChart);
                        chart.scrollbarX = new am4core.Scrollbar();

                        // Add data
                        chart.data = [{
                            "country": "Proposal",
                            "visits": data[0]
                        }, {
                            "country": "Seminar",
                            "visits": data[1]
                        }, {
                            "country": "Skripsi",
                            "visits": data[2]
                        }, ];

                        // Create axes
                        var categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
                        categoryAxis.dataFields.category = "country";
                        categoryAxis.renderer.grid.template.location = 0;
                        categoryAxis.renderer.minGridDistance = 30;
                        categoryAxis.renderer.labels.template.horizontalCenter = "right";
                        categoryAxis.renderer.labels.template.verticalCenter = "middle";
                        categoryAxis.renderer.labels.template.rotation = 270;
                        categoryAxis.tooltip.disabled = true;
                        categoryAxis.renderer.minHeight = 110;

                        var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
                        valueAxis.renderer.minWidth = 50;

                        // Create series
                        var series = chart.series.push(new am4charts.ColumnSeries());
                        series.sequencedInterpolation = true;
                        series.dataFields.valueY = "visits";
                        series.dataFields.categoryX = "country";
                        series.tooltipText = "[{categoryX}: bold]{valueY}[/]";
                        series.columns.template.strokeWidth = 0;

                        series.tooltip.pointerOrientation = "vertical";

                        series.columns.template.column.cornerRadiusTopLeft = 10;
                        series.columns.template.column.cornerRadiusTopRight = 10;
                        series.columns.template.column.fillOpacity = 0.8;

                        // on hover, make corner radiuses bigger
                        var hoverState = series.columns.template.column.states.create("hover");
                        hoverState.properties.cornerRadiusTopLeft = 0;
                        hoverState.properties.cornerRadiusTopRight = 0;
                        hoverState.properties.fillOpacity = 1;

                        series.columns.template.adapter.add("fill", function(fill, target) {
                            return chart.colors.getIndex(target.dataItem.index);
                        });

                        // Cursor
                        chart.cursor = new am4charts.XYCursor();

                    }); // end am4core.ready()
                    // End Chart
                })
            })
        })


    })
</script>
<?php $this->app->endSection('script') ?>

<?php $this->app->init() ?>