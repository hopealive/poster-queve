var timeDelay = {
    'normal': 2000,
    'empty': 30000,
    'error': 30000,
};

function updateData() {
    var success = function (response) {
        $('.error-block .error-message').html('');
        $('.error-block').hide();

        if (jQuery.isEmptyObject(response) || response.status != 'success') {
            $('.order-body').empty().append(vRow);

            var errorMeassge = "Сталась невідома помилка";
            if ( response.message && response.message.length > 0 ){
                errorMeassge = response.message;
            }
            $('.error-block .error-message').html(errorMeassge);
            $('.error-block').show();
            return;
        }

        if (response.status == 'success') {
            if (response.transactions.length > 0) {
                $('.order-body').empty();

                $(response.transactions).each(function (k, row) {
                    var vRow = '';

                    if (row['status'] == 'done' || row['status'] == 'closed_done' ) {
                        vRow += '<div class="row status-complete">'
                                + '<div class="col col-id">' + row['view_id'] + '</div>'
                                + '<div class="col col-status">Готово</div>'
                                + '</div>';
                    } else if (row['status'] == 'waiting') {
                        vRow += '<div class="row status-in-progress">'
                                + '<div class="col col-id">' + row['view_id'] + '</div>'
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

            if ( response.status_changed_to_done ){
                var audio = new Audio('/public/sounds/notification.mp3');
                audio.play();
            }
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
    $('.error-block .error-block-close').on('click', function(){
        $('.error-block').hide();
    });
});

