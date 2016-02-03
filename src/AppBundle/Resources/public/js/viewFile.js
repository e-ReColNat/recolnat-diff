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
            .attr("data-url", function(d,i){
                return Routing.generate(keys[itkeys]!='specimens' ? keys[itkeys] : 'todos', { 'institutionCode': institutionCode, 'collectionCode' : collectionCode}) ;
            })
            .attr("class", function(d,i){
                return datas[i].name;
            })
            .attr("width", x)
            .attr("height", barHeight-2 )
            .on('mouseover', function(data) {
                d3.select(this).classed('over', true);
            })

            .on('mouseout', function(data) {
                d3.select(this).classed('over', false);
            })
            .on('click', function(data) {
                window.location = d3.select(this).attr("data-url");
            });

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
