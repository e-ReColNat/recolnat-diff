var width = 900,
    barHeight = 20;

var diffs = datas.map(function (obj) {
    return obj.differences;
});
var keys = ['name', 'specimens', 'differences', 'todos', 'choices', 'excluRecolnat', 'excluInstitution'];
var routes = {
    specimens:{name : 'diffs', params:['selectedClassName']},
    differences:{name : 'diffs', params:['selectedClassName']},
    todos:{name : 'todos', params:['selectedClassName']},
    choices:{name : 'choices', params:['selectedClassName']},
    excluRecolnat:{name : 'lonesomes', params:['selectedClassName', 'db']},
    excluInstitution:{name : 'lonesomes', params:['selectedClassName', 'db']}
};

var x = d3.scale.linear()
    .domain([0, d3.max(diffs)+30])
    .range([0, width-(15*8)]); // soustraction de la longueur du texte

var chart = d3.select(".chart")
    .attr("width", width)
    .attr("height", barHeight * datas.length * 7);

var classesName = datas.map(function(obj) {
    return obj.name;
});

var groupBar = chart.selectAll("g") ;
var selectedClassName='';
var optParams = {
    'institutionCode': institutionCode,
    'collectionCode' : collectionCode
};
var bar;
for (var itkeys = 0; itkeys < keys.length; itkeys++) {
    var selectedDatas = datas.map(function (obj) {
        return obj[keys[itkeys]];
    });
    if (keys[itkeys] != 'name') {
        bar = groupBar
            .data(selectedDatas)
            .enter().append("g")
            .attr("transform", function (d, i) {
                var decalY = 7 * i * barHeight + ((itkeys)*20);
                return "translate(10," + decalY + ")";
            });

        bar.append("rect")
            .attr("data-url", function(d,i){
                optParams['selectedClassName']=classesName[i];
                if (routes[keys[itkeys]].name=='lonesomes') {
                    if (keys[itkeys] == 'excluRecolnat') {
                        optParams["db"] = 'recolnat';
                    }
                    else {
                        optParams["db"] = 'institution';
                    }
                }
                return Routing.generate(routes[keys[itkeys]].name, optParams) ;
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
                return Translator.transChoice('label.graph.'+keys[itkeys], d, { "count" : d });
            });
    }
    else {

        bar = groupBar
            .data(selectedDatas)
            .enter().append("g")
            .attr("transform", function (d, i) {
                var decalY = 7 * i * barHeight + ((itkeys)*20);
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
                return Translator.transChoice('label.'+datas[i].name, 2, {}, 'entity');
            })
            .attr("class", function(d,i){
                return 'title ' +datas[i].name;
            });
    }
}
