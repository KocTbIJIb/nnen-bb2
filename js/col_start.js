$(document).ready(function(){
    var url = "http://nnen.ru/colonization/start";
    var data = {};
    
    var team_hash = $(".team_hash");
    if (!team_hash.length || team_hash.val().length < 32) {
        alert('Unauthorized');
        return;
    }
    data.team_hash = team_hash.val();
    data.sectors = collectSectors();
    //data.units = collectUnits();

    postCORS(url, data, responseProcessing);
});

function collectSectors() {
    var codes = [];
    $('.cols-wrapper .cols .color_correct').each(function(i){
        codes.push($(this).html());
    });
    return codes;
}

function collectUnits() {
    var codes = [];
    $('.mr2-sc-units').each(function(i){
        codes.push($(this).val());
    });
    return codes;
}

function responseProcessing(data) {
    if (typeof(data) == "string") {
        data = eval("(" + data + ")");
    }
    switch (data.status) {
        case 'win':
            $('#win-code').html('Проходной код: ' + data.code);
            break;
        case 'error':
            alert(data.error);
            break;
        case 'game':
        default:
            $('#game-info').append('<h2>Команда: ' + data.team + '</h2><p>Построеные объекты:</p><ul></ul>');
            $.each(data.objects, function(index, value) {
                $('#game-info ul').append('<li>' + value.name + '</li>');    
            })
            if (data.message.length > 0) {
                $('#win-code').html(data.message.join('; '));
            }
            break;
    }
}
