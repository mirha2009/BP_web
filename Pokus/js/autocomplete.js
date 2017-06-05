var hotel_field = [];
/*Funkcni pres ajax a vypada to lepe*/
function query_ajax(letters) {
    if (letters === "") {
        letters = "villa";
    }
    $.ajax({
        url: 'http://localhost/ddd/query.php?term=' + letters,
        //type: 'POST',
        //dataType: "json",
        complete: function (response) {
            var output = replace_quotes(response.responseText);
            hotel_field = output.split(",");
            //document.getElementById("result").innerHTML = output + "<br>" + "delka je: " + hotel_field.length;
            // document.getElementById("result").innerHTML =hotel_field.length;
            return hotel_field;
            //alert("Delka pole je: " + hotel_field.length);
        },
        error: function () {
            alert("There was an error!");
        }
    });
    return hotel_field;
}

function first_autocomplete(letters){
    var hotel_names = [];
    hotel_names = query_ajax(letters);
    $("#nameField").autocomplete({
        source: hotel_names
    });
}
function second_autocomplete(letters){
    var hotel_names = [];
    hotel_names = query_ajax(letters);
    $("#nameField2").autocomplete({
        source: hotel_names,
        // minLength: 3
    });
}