<!DOCTYPE html>
<html>
    <head>
        <title>Комора. Перша галушкова мануфактура</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style type="text/css">
            *, html, body { background-color: #E6E6E6; }
            table.orders { display: table;width: 100%; border: 0px;border-spacing:0;}
            table.orders td { margin: 0; font-size: 26px; font-style: italic; font-weight: bolder; }

            .status-in-progress { background-color:#FFFF00; color: #000; }
            .status-complete { background-color:#00FF00; color: #000; }


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
            <table class="orders">
                <thead>
                <td>#</td>
                <td>Статус</td>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="loader">&nbsp;</div>
                        </td>
                    </tr>
                </tbody>
            </table>

        </div>

        <script src="https://code.jquery.com/jquery-3.2.1.min.js" crossorigin="anonymous"></script>
        <script>

            function updateData() {
                var success = function (response) {
                    if ( response.data.length > 0 ){
                        $('table.orders tbody').html(response.data);
                    }
                    window.setInterval('updateData()', 60000);
                };
                var error = function (error) {
                    console.error(error);
                };

                $.ajax({
                    url: 'ajax.php',
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
