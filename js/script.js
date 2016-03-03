document.addEventListener("DOMContentLoaded", function (event) {
    document.getElementById('search-it').onclick = function () {
        search();
        return false;

    };
    document.getElementById('form_search').onsubmit = function () {
        search();
        return false;
    }
});


function callAjax(url, callback) {
    var xmlhttp;
    xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            callback(xmlhttp.responseText);
        }
    };
    xmlhttp.open("POST", url, true);
    xmlhttp.send("fname=Henry&lname=Ford");
}

function search(){
    var search_button =document.getElementById('search-it');
    search_button.innerHTML = search_button.getAttribute('data-busy');
    var term =document.getElementById('search').value;


    callAjax('/api.php?term=' + term, function (response) {
        var result = JSON.parse(response);

        var table = document.getElementById('result');
        table.innerHTML = '';

        var headers = Object.keys(result[0]);

        //show headers
        var tr = table.insertRow();
        for (i = 0; i < headers.length; i++){
            var td = tr.insertCell(-1);
            td.innerHTML = headers[i];
        }

        //show rows
        for (var i = 0; i < result.length; i++) {
            var tr = table.insertRow();
            for (j = 0; j < headers.length; j++){
                var cell = tr.insertCell(-1);
                var header = headers[j];
                if (header=='image_url'){
                    var img = document.createElement('img');
                    img.src = result[i][header];
                    img.style = 'width:100px;'
                    cell.appendChild(img);
                }else{
                    cell.innerHTML = result[i][header];
                }

            }
        }
        search_button.innerHTML = search_button.getAttribute('data-ready');
    });

    return false;
}