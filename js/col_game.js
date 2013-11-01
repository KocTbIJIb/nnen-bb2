$(document).ready(function(){
    var url = "http://nnen.ru/colonization/game";
    var data = {};
    
    var team_hash = $(".team_hash").eq(0);
    if (!team_hash.length || team_hash.val().length < 32) {
        alert('Unauthorized');
        return;
    }
    data.team_hash = team_hash.val();
    data.sectors = collectSectors();
    data.resources = collectBonuses();

    postCORS(url, data, responseProcessing);
});

function collectSectors() {
    var codes = [];
    $('.cols-wrapper .cols .color_correct').each(function(i){
        codes.push($(this).html());
    });
    return codes;
}

function collectBonuses() {
    var codes = [];
    $('h3.color_correct').each(function(i){
        codes.push($(this).next().html());
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

            $('#game-balance').append('<h2><p>Игровой баланс:</p><ul></ul>');
            $('#game-balance ul').append('<li>Дерево: ' + (data.balance ? parseInt(data.balance.wood) : 0) + '</li>');
            $('#game-balance ul').append('<li>Камень: ' + (data.balance ? parseInt(data.balance.stone) : 0) + '</li>');
            $('#game-balance ul').append('<li>Лён: ' + (data.balance ? parseInt(data.balance.flax) : 0) + '</li>');
            $('#game-balance ul').append('<li>Вода: ' + (data.balance ? parseInt(data.balance.water) : 0) + '</li>');
            $('#game-balance ul').append('<li>Очки: ' + (data.balance ? parseInt(data.balance.total) : 0) + '</li>');

            if (data.message.length > 0) {
                $('#win-code').html(data.message.join('; '));
            }
            break;
    }
}
