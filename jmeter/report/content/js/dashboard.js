/*
   Licensed to the Apache Software Foundation (ASF) under one or more
   contributor license agreements.  See the NOTICE file distributed with
   this work for additional information regarding copyright ownership.
   The ASF licenses this file to You under the Apache License, Version 2.0
   (the "License"); you may not use this file except in compliance with
   the License.  You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
*/
var showControllersOnly = false;
var seriesFilter = "";
var filtersOnlySampleSeries = true;

/*
 * Add header in statistics table to group metrics by category
 * format
 *
 */
function summaryTableHeader(header) {
    var newRow = header.insertRow(-1);
    newRow.className = "tablesorter-no-sort";
    var cell = document.createElement('th');
    cell.setAttribute("data-sorter", false);
    cell.colSpan = 1;
    cell.innerHTML = "Requests";
    newRow.appendChild(cell);

    cell = document.createElement('th');
    cell.setAttribute("data-sorter", false);
    cell.colSpan = 3;
    cell.innerHTML = "Executions";
    newRow.appendChild(cell);

    cell = document.createElement('th');
    cell.setAttribute("data-sorter", false);
    cell.colSpan = 7;
    cell.innerHTML = "Response Times (ms)";
    newRow.appendChild(cell);

    cell = document.createElement('th');
    cell.setAttribute("data-sorter", false);
    cell.colSpan = 1;
    cell.innerHTML = "Throughput";
    newRow.appendChild(cell);

    cell = document.createElement('th');
    cell.setAttribute("data-sorter", false);
    cell.colSpan = 2;
    cell.innerHTML = "Network (KB/sec)";
    newRow.appendChild(cell);
}

/*
 * Populates the table identified by id parameter with the specified data and
 * format
 *
 */
function createTable(table, info, formatter, defaultSorts, seriesIndex, headerCreator) {
    var tableRef = table[0];

    // Create header and populate it with data.titles array
    var header = tableRef.createTHead();

    // Call callback is available
    if(headerCreator) {
        headerCreator(header);
    }

    var newRow = header.insertRow(-1);
    for (var index = 0; index < info.titles.length; index++) {
        var cell = document.createElement('th');
        cell.innerHTML = info.titles[index];
        newRow.appendChild(cell);
    }

    var tBody;

    // Create overall body if defined
    if(info.overall){
        tBody = document.createElement('tbody');
        tBody.className = "tablesorter-no-sort";
        tableRef.appendChild(tBody);
        var newRow = tBody.insertRow(-1);
        var data = info.overall.data;
        for(var index=0;index < data.length; index++){
            var cell = newRow.insertCell(-1);
            cell.innerHTML = formatter ? formatter(index, data[index]): data[index];
        }
    }

    // Create regular body
    tBody = document.createElement('tbody');
    tableRef.appendChild(tBody);

    var regexp;
    if(seriesFilter) {
        regexp = new RegExp(seriesFilter, 'i');
    }
    // Populate body with data.items array
    for(var index=0; index < info.items.length; index++){
        var item = info.items[index];
        if((!regexp || filtersOnlySampleSeries && !info.supportsControllersDiscrimination || regexp.test(item.data[seriesIndex]))
                &&
                (!showControllersOnly || !info.supportsControllersDiscrimination || item.isController)){
            if(item.data.length > 0) {
                var newRow = tBody.insertRow(-1);
                for(var col=0; col < item.data.length; col++){
                    var cell = newRow.insertCell(-1);
                    cell.innerHTML = formatter ? formatter(col, item.data[col]) : item.data[col];
                }
            }
        }
    }

    // Add support of columns sort
    table.tablesorter({sortList : defaultSorts});
}

$(document).ready(function() {

    // Customize table sorter default options
    $.extend( $.tablesorter.defaults, {
        theme: 'blue',
        cssInfoBlock: "tablesorter-no-sort",
        widthFixed: true,
        widgets: ['zebra']
    });

    var data = {"OkPercent": 100.0, "KoPercent": 0.0};
    var dataset = [
        {
            "label" : "FAIL",
            "data" : data.KoPercent,
            "color" : "#FF6347"
        },
        {
            "label" : "PASS",
            "data" : data.OkPercent,
            "color" : "#9ACD32"
        }];
    $.plot($("#flot-requests-summary"), dataset, {
        series : {
            pie : {
                show : true,
                radius : 1,
                label : {
                    show : true,
                    radius : 3 / 4,
                    formatter : function(label, series) {
                        return '<div style="font-size:8pt;text-align:center;padding:2px;color:white;">'
                            + label
                            + '<br/>'
                            + Math.round10(series.percent, -2)
                            + '%</div>';
                    },
                    background : {
                        opacity : 0.5,
                        color : '#000'
                    }
                }
            }
        },
        legend : {
            show : true
        }
    });

    // Creates APDEX table
    createTable($("#apdexTable"), {"supportsControllersDiscrimination": true, "overall": {"data": [0.1, 500, 1500, "Total"], "isController": false}, "titles": ["Apdex", "T (Toleration threshold)", "F (Frustration threshold)", "Label"], "items": [{"data": [0.2, 500, 1500, "TC04.1 GET /login - Lấy CSRF token"], "isController": false}, {"data": [0.225, 500, 1500, "TC01 GET / - Trang chủ tải thành công"], "isController": false}, {"data": [0.0, 500, 1500, "TC03.2 POST /login - Đăng nhập admin hợp lệ-1"], "isController": false}, {"data": [0.0, 500, 1500, "TC03.2 POST /login - Đăng nhập admin hợp lệ-0"], "isController": false}, {"data": [0.0, 500, 1500, "TC03.3 GET /dashboard - Tải đồng thời sau đăng nhập"], "isController": false}, {"data": [0.6, 500, 1500, "TC02 GET /dashboard - Chưa đăng nhập bị chuyển hướng"], "isController": false}, {"data": [0.1, 500, 1500, "TC03.1 GET /login - Lấy CSRF token"], "isController": false}, {"data": [0.0, 500, 1500, "TC04.2 POST /login - Sai mật khẩu hiển thị lỗi-1"], "isController": false}, {"data": [0.0, 500, 1500, "TC04.2 POST /login - Sai mật khẩu hiển thị lỗi-0"], "isController": false}, {"data": [0.0, 500, 1500, "TC03.2 POST /login - Đăng nhập admin hợp lệ"], "isController": false}, {"data": [0.0, 500, 1500, "TC04.2 POST /login - Sai mật khẩu hiển thị lỗi"], "isController": false}]}, function(index, item){
        switch(index){
            case 0:
                item = item.toFixed(3);
                break;
            case 1:
            case 2:
                item = formatDuration(item);
                break;
        }
        return item;
    }, [[0, 0]], 3);

    // Create statistics table
    createTable($("#statisticsTable"), {"supportsControllersDiscrimination": true, "overall": {"data": ["Total", 95, 0, 0.0, 5697.63157894737, 408, 19636, 4232.0, 12423.000000000002, 18793.8, 19636.0, 1.6691850862705133, 40.45757702959729, 1.2009278802227923], "isController": false}, "titles": ["Label", "#Samples", "FAIL", "Error %", "Average", "Min", "Max", "Median", "90th pct", "95th pct", "99th pct", "Transactions/s", "Received", "Sent"], "items": [{"data": ["TC04.1 GET /login - Lấy CSRF token", 5, 0, 0.0, 2102.2, 525, 3684, 2112.0, 3684.0, 3684.0, 3684.0, 0.9464319515426841, 7.192513131743327, 0.1127584942267651], "isController": false}, {"data": ["TC01 GET / - Trang chủ tải thành công", 20, 0, 0.0, 1773.8500000000001, 408, 2940, 1853.5, 2813.3, 2933.95, 2940.0, 2.2581009371118888, 6.509681607767868, 0.25800567347860454], "isController": false}, {"data": ["TC03.2 POST /login - Đăng nhập admin hợp lệ-1", 10, 0, 0.0, 8285.499999999998, 3926, 12257, 8566.5, 12179.800000000001, 12257.0, 12257.0, 0.36957646537068517, 24.65948437153522, 0.3111083136225885], "isController": false}, {"data": ["TC03.2 POST /login - Đăng nhập admin hợp lệ-0", 10, 0, 0.0, 7901.799999999999, 1753, 12672, 8698.5, 12565.300000000001, 12672.0, 12672.0, 0.43497172683775553, 0.6477850424097434, 0.4345469497607656], "isController": false}, {"data": ["TC03.3 GET /dashboard - Tải đồng thời sau đăng nhập", 10, 0, 0.0, 6515.8, 4665, 11878, 5437.5, 11656.7, 11878.0, 11878.0, 0.3596734165377837, 23.99871697748444, 0.3027719580620796], "isController": false}, {"data": ["TC02 GET /dashboard - Chưa đăng nhập bị chuyển hướng", 5, 0, 0.0, 576.8, 493, 613, 597.0, 613.0, 613.0, 613.0, 2.2665457842248413, 3.3312025441976427, 0.27889137579329104], "isController": false}, {"data": ["TC03.1 GET /login - Lấy CSRF token", 10, 0, 0.0, 3782.7000000000003, 519, 6353, 3887.0, 6290.6, 6353.0, 6353.0, 0.9214042200313277, 7.002312148714641, 0.1097766746521699], "isController": false}, {"data": ["TC04.2 POST /login - Sai mật khẩu hiển thị lỗi-1", 5, 0, 0.0, 2888.2, 1558, 3703, 3406.0, 3703.0, 3703.0, 3703.0, 0.5301664722722935, 4.104130493850069, 0.4442215168062772], "isController": false}, {"data": ["TC04.2 POST /login - Sai mật khẩu hiển thị lỗi-0", 5, 0, 0.0, 3678.4, 1867, 4982, 4174.0, 4982.0, 4982.0, 4982.0, 0.513347022587269, 0.7544797548767967, 0.5158535998459959], "isController": false}, {"data": ["TC03.2 POST /login - Đăng nhập admin hợp lệ", 10, 0, 0.0, 16187.7, 5680, 19636, 18666.0, 19611.2, 19636.0, 19636.0, 0.3470776065528252, 23.67516681417465, 0.6389075081563237], "isController": false}, {"data": ["TC04.2 POST /login - Sai mật khẩu hiển thị lỗi", 5, 0, 0.0, 6567.0, 5283, 7878, 6547.0, 7878.0, 7878.0, 7878.0, 0.44255620463798906, 4.076357541157727, 0.8155308185077005], "isController": false}]}, function(index, item){
        switch(index){
            // Errors pct
            case 3:
                item = item.toFixed(2) + '%';
                break;
            // Mean
            case 4:
            // Mean
            case 7:
            // Median
            case 8:
            // Percentile 1
            case 9:
            // Percentile 2
            case 10:
            // Percentile 3
            case 11:
            // Throughput
            case 12:
            // Kbytes/s
            case 13:
            // Sent Kbytes/s
                item = item.toFixed(2);
                break;
        }
        return item;
    }, [[0, 0]], 0, summaryTableHeader);

    // Create error table
    createTable($("#errorsTable"), {"supportsControllersDiscrimination": false, "titles": ["Type of error", "Number of errors", "% in errors", "% in all samples"], "items": []}, function(index, item){
        switch(index){
            case 2:
            case 3:
                item = item.toFixed(2) + '%';
                break;
        }
        return item;
    }, [[1, 1]]);

        // Create top5 errors by sampler
    createTable($("#top5ErrorsBySamplerTable"), {"supportsControllersDiscrimination": false, "overall": {"data": ["Total", 95, 0, "", "", "", "", "", "", "", "", "", ""], "isController": false}, "titles": ["Sample", "#Samples", "#Errors", "Error", "#Errors", "Error", "#Errors", "Error", "#Errors", "Error", "#Errors", "Error", "#Errors"], "items": [{"data": [], "isController": false}, {"data": [], "isController": false}, {"data": [], "isController": false}, {"data": [], "isController": false}, {"data": [], "isController": false}, {"data": [], "isController": false}, {"data": [], "isController": false}, {"data": [], "isController": false}, {"data": [], "isController": false}, {"data": [], "isController": false}, {"data": [], "isController": false}]}, function(index, item){
        return item;
    }, [[0, 0]], 0);

});
