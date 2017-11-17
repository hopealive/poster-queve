<!DOCTYPE html>
<html>
    <head>
        <title>Комора. Перша галушкова мануфактура</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style type="text/css">
            *, html, body { background-color: #E6E6E6; margin: 0; padding: 0;}

            .row { width: 960px; clear: both; font-size: 22px; font-style: italic; font-weight: bolder;}
            .col { float: left }
            .col.col-id { width: 50px }
            .col.col-status { padding-left: 20px; width: 890px }

            .status-in-progress .col { background-color:#FFFF00; color: #000; }
            .status-complete .col { background-color:#00FF00; color: #000; }


            .loader {
                border: 16px solid #f3f3f3;
                border-top: 16px solid #3498db;
                border-radius: 50%;
                width: 120px;
                height: 120px;
                animation: spin 2s linear infinite;
            }

            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }

        </style>
    </head>
    <body>
        <div class="order-data">
            <div class="orders">


                <div class="row">
                    <div class="col col-id">#</div>
                    <div class="col col-status">Статус</div>
                </div>

                <div class="order-body">
                    <div class="loader">&nbsp;</div>
                </div>
            </div>

        </div>

        <script src="https://code.jquery.com/jquery-3.2.1.min.js" crossorigin="anonymous"></script>
        <script>

            var timeDelay = {
                'normal': 3000,
                'empty': 30000,
                'error': 30000,
            };

            function updateData() {
                var success = function (response) {
                    if ( response.length > 0 ){
                        $('.order-body').empty();

                        $(response).each(function(k, row){
                            var vRow = '';

                            if(row['status'] == 2 ){
                                vRow += '<div class="row status-complete">';
                            } else {
                                vRow += '<div class="row status-in-progress"">';
                            }

                            vRow += '<div class="col col-id">'+row['id']+'</div>';

                            if(row['status'] == 2 ){
                                vRow += '<div class="col col-status">Готово</div>';
                            } else {
                                vRow += '<div class="col col-status">Очікування</div>';
                            }
                            vRow += '</div>';
                            $('.order-body').append(vRow);
                        });
                        window.setTimeout('updateData()', timeDelay.normal);
                    } else {
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
        </script>

    </body>
</html>
