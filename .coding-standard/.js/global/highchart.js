$(document).ready(function(){
    window.highchart_pie = function(id,data){
        Highcharts.chart(id, {
            credits: {
              enabled: false
            },
            chart: {
              renderTo: 'container',
              type: 'pie',
              marginTop: 20,
              marginRight: 10,
              marginLeft: 10,
              marginBottom: 20,
              spacingTop: 0,
              spacingBottom: 0,
              spacingLeft: 0,
              spacingRight: 0,
            },
            plotOptions: {
              pie: {
                size: '100%',
                center: ['50%', '50%'],
                borderWidth: 0,
                dataLabels: {
                  enabled: true,
                  distance: -30,
                  color: '#333333',
                  style: {
                      fontSize: '12px'
                  },
                  formatter: function() {
                    return '<b>' + this.point.name + '</b>: <br/>' + this.point.y+ " outlet" ;
                  }
                },
                data: data
              }
            },
            title: {
              text: ''
            },
            series: [
              {
                type: 'pie',
                name: 'Outlet',
                size: '45%',
                dataLabels: {
                  distance: -50,
                  formatter: function() {
                    if (this.percentage != 0) return Math.round(this.percentage) + '%'
                  }
                },
                style: {
                  fontSize: '20px'
                }
              },
              {
                type: 'pie',
                name: 'Outlet',
                colorByPoint: true,
                size: '50%',
                innerSize: '50%',
                dataLabels: {
                  distance: 20,
                },
                style: {
                  fontSize: '20px'
                }
              }
            ]
          });
    }
    window.highchart_line = function(){

    }
    window.highchart_bar = function(){
        
    }
    window.highchart_line_drilldown_pie = function(){

    }
});
$(function() {
    window.highchart_line_drilldown_pie = function(id,title,chartSeries,drilldown){
      // Create the chart
      Highcharts.chart(id, {
        chart: {
          type: 'line',// column/line
          events: {
            drilldown: function(e) {
              if (!e.seriesOptions) {
                var chart = this,
                  drilldowns = drilldown
                  // {
                  //   'Animals1': {
                  //     name: 'Animal1',
                  //     type: 'pie',
                  //     data: [
                  //       ['Cows', 2],
                  //       ['Sheep', 3]
                  //     ]
                  //   },
                  //   'Animals2': {
                  //     name: 'Animals2',
                  //     type: 'pie',
                  //     color: Highcharts.getOptions().colors[1],
                  //     data: [
                  //       ['Cows', 22],
                  //       ['Sheep', 13]
                  //     ]
                  //   },
                  //   'Fruits': {
                  //     name: 'Fruits',
                  //     data: [
                  //       ['Apples', 5],
                  //       ['Oranges', 7],
                  //       ['Bananas', 2]
                  //     ]
                  //   },
                  //   'Fruits2': {
                  //     name: 'Fruits',
                  //     color: 'red',
                  //     data: [
                  //       ['Apples', 15],
                  //       ['Oranges', 17],
                  //       ['Bananas', 22]
                  //     ]
                  //   },
                  //   'Cars': {
                  //     name: 'Cars',
                  //     data: [
                  //       ['Toyota', 1],
                  //       ['Volkswagen', 2],
                  //       ['Opel', 5]
                  //     ]
                  //   },
                  //   'Cars2': {
                  //     name: 'Cars',
                  //     color: '#bada55',
                  //     data: [
                  //       ['Toyota', 11],
                  //       ['Volkswagen', 21],
                  //       ['Opel', 15]
                  //     ]
                  //   }
                  // }
                  ,
                  series = [drilldowns[e.point.drilldownName], drilldowns[e.point.drilldownName + '2']];

                chart.addSingleSeriesAsDrilldown(e.point, series[0]);
                // chart.addSingleSeriesAsDrilldown(e.point, series[1]);
                chart.applyDrilldown();
              }

            }
          }
        },
        title: {
          text: title
        },

        legend: {
          enabled: false
        },

        plotOptions: {
          series: {
            borderWidth: 0,
            dataLabels: {
            
              enabled: true,
              color: '#333333',
              style: {
                  fontSize: '12px'
              },
              formatter: function() {
                return '<b>' + this.point.name + '</b>: <br/>' + this.point.y+ " %" ;
              }

            },
           
          }
        },
        series: chartSeries
        // [{
        //   name: 'Things1',
        //     data: [{
        //       name: 'Animals 1',
        //       y: 0,
        //       x: 1,
        //       drilldownName: 'Animals1',
        //       drilldown: true
        //     }, {
        //       name: 'Animal 2',
        //       y: 2,
        //       x: 2,
        //       drilldownName: 'Animals2',
        //       drilldown: true
        //     }, {
        //       name: 'Animal 3',
        //       y: 1,
        //       x: 3,
        //       drilldownName: 'Animals2',
        //       drilldown: true
        //     }]
        //   }, {
        //   name: 'Things2',
        //     data: [{
        //       name: 'Fruit 1',
        //       y: 3,
        //       x: 1,
        //       drilldownName: 'Fruits',
        //       drilldown: true
        //     }, {
        //       name: 'Fruit 2',
        //       y: 4,
        //       x: 2,
        //       drilldownName: 'Fruits',
        //       drilldown: true
        //     }, {
        //       name: 'Fruit 3',
        //       y: 5,
        //       x: 3,
        //       drilldownName: 'Fruits',
        //       drilldown: true
        //     }]
        //   }, {
        //   name: 'Things3',
        //     data: [{
        //       name: 'Car 3',
        //       y: 6,
        //       x: 1,
        //       drilldownName: 'Cars',
        //       drilldown: true
        //     }, {
        //       name: 'Car 2',
        //       y: 7,
        //       x: 2,
        //       drilldownName: 'Cars',
        //       drilldown: true
        //     }, {
        //       name: 'Car 1',
        //       y: 8,
        //       x: 3,
        //       drilldownName: 'Cars',
        //       drilldown: true
        //     }]
        //   }]
          ,

        drilldown: {
          series: []
        }
      });
    }
  });