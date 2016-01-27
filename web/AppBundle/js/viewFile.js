var width = 900,
    barHeight = 20;

var diffs = datas.map(function (obj) {
    return obj.diffs;
});
var keys = ['name', 'specimens', 'diffs', 'todos', 'choices'];
var classesName = datas.map(function(obj) {
    return obj.name;
});

var x = d3.scale.linear()
    .domain([0, d3.max(diffs)+30])
    .range([0, width]);

var chart = d3.select(".chart")
    .attr("width", width)
    .attr("height", barHeight * datas.length * 5);

var groupBar = chart.selectAll("g") ;


for (var itkeys = 0; itkeys < keys.length; itkeys++) {
    var selectedDatas = datas.map(function (obj) {
        return obj[keys[itkeys]];
    });
    if (keys[itkeys] != 'name') {
        var bar = groupBar
            .data(selectedDatas)
            .enter().append("g")
            .attr("transform", function (d, i) {
                decalY = 5 * i * barHeight + ((itkeys)*20);
                return "translate(10," + decalY + ")";
            });

        bar.append("rect")
            .attr("class", function(d,i){
                return datas[i].name;
            })
            .attr("width", x)
            .attr("height", barHeight - 1);

        bar.append("text")
            .attr("x", function (d) {
                return x(d) + 12;
            })
            .attr("y", barHeight / 2)
            .attr("dy", ".35em")
            .text(function (d, i) {
                title = d+' '+keys[itkeys] ;
                return title;
            });
    }
    else {
        var bar = groupBar
            .data(selectedDatas)
            .enter().append("g")
            .attr("transform", function (d, i) {
                decalY = 5 * i * barHeight + ((itkeys)*20);
                return "translate(10," + decalY + ")";
            });

        /*bar.append("rect")
            .attr("class", function(d,i){
                return "title "+datas[i].name;
            })
            .attr("width", 400)
            .attr("height", barHeight - 1);*/

        bar.append("text")
            .attr("x", function (d) {
                return 400 /2;
            })
            .attr("class", function(d,i){
                return "title "+datas[i].name;
            })
            .attr("y", barHeight / 2)
            .attr("dy", ".35em")
            .text(function (d, i) {
                return datas[i].name;
            });
    }
}


/*var bar = chart.selectAll("g")
 .data(datas, function(d) {console.log(d);return d;})
 .enter().
 data(datas, function(d) {console.log(d);return d;}).append("g")
 .attr("transform", function(d, i) { console.log((i*2) * barHeight);return "translate(0," + (i*2) * barHeight + ")"; });
 */
//var bar = chart.selectAll("g")
//    .data(diffs)
//    .enter().append("g")
//    .attr("transform", function(d, i) { console.log((i*2) * barHeight);return "translate(0," + (i*2) * barHeight + ")"; });
//
//bar.append("rect")
//    .attr("width", x)
//    .attr("height", barHeight - 1);
//
//bar.append("text")
//    .attr("x", function(d) { return x(d) - 3; })
//    .attr("y", barHeight / 2)
//    .attr("dy", ".35em")
//    .text(function(d, i) { return datas[i].name; });

//var data = [4, 8, 15, 16, 23, 42];
//
//var width = 420,
//    barHeight = 20;
//
//console.log(diffs);
//var x = d3.scale.linear()
//    .domain([0, d3.max(data)])
//    .range([0, width]);
//
//var chart = d3.select(".chart")
//    .attr("width", width)
//    .attr("height", barHeight * data.length);
//
//var bar = chart.selectAll("g")
//    .data(data)
//    .enter().append("g")
//    .attr("transform", function(d, i) { return "translate(0," + i * barHeight + ")"; });
//
//bar.append("rect")
//    .attr("width", x)
//    .attr("height", barHeight - 1);
//
//bar.append("text")
//    .attr("x", function(d) { return x(d) - 3; })
//    .attr("y", barHeight / 2)
//    .attr("dy", ".35em")
//    .text(function(d) { return d; });