var timeDelay = {
    'normal': 2000,
    'empty': 30000,
    'error': 30000,
};

function updateData() {
    var success = function (response) {
        if (response.length > 0) {
            $('.order-body').empty();

            $(response).each(function (k, row) {
                var vRow = '';

                if (row['status'] == 102) {
                    vRow += '<div class="row status-complete">'
                        + '<div class="col col-id">' + row['id'] + '</div>'
                        + '<div class="col col-status">Готово</div>'
                        + '</div>';
                } else {
                    vRow += '<div class="row status-in-progress">'
                        + '<div class="col col-id">' + row['id'] + '</div>'
                        + '<div class="col col-status">Очікування</div>'
                        + '</div>';
                }

                $('.order-body').append(vRow);
            });
            window.setTimeout('updateData()', timeDelay.normal);
        } else {
            var vRow = '<div class="row status-in-progress">'
                        + '<div class="col col-id">&nbsp;</div>'
                        + '<div class="col col-status">Немає замовлень</div>'
                        + '</div>';
                
            $('.order-body').empty().append(vRow);
            window.setTimeout('updateData()', timeDelay.empty);
        }
    };
    var error = function (error) {
        console.error(error);
        window.setTimeout('updateData()', timeDelay.error);
    };

    $.ajax({
        url: 'ajax_orders.php',
        dataType: "json",
        success: success,
        error: error
    });
}

$('document').ready(function () {
    updateData();
});

