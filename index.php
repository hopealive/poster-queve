<!DOCTYPE html>
<html>
    <head>
        <title>Комора. Перша галушкова мануфактура</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link href="js/jsImgSlider/themes/6/js-image-slider.css" rel="stylesheet" type="text/css" />
        <link href="css/style.css" rel="stylesheet" type="text/css" />
    </head>
    <body>

        <div class="wrapper">
            <div class="full-hd-col order-data">
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

            <div class="full-hd-col poster-slider">
                <div id="sliderFrame">
                    <div id="slider">
                        <?php
                        for ($i = 1; $i <= 12; $i++) {
                            echo '<img src="images/photo/'.$i.'.jpg" />';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://code.jquery.com/jquery-3.2.1.min.js" crossorigin="anonymous"></script>
        <script src="js/custom.js" type="text/javascript"></script>
        <script src="js/jsImgSlider/themes/6/mcVideoPlugin.js" type="text/javascript"></script>
        <script src="js/jsImgSlider/themes/6/js-image-slider.js" type="text/javascript"></script>
        <script>
            var sliderOptions =
                    {
                        sliderId: "slider",
                        startSlide: 0,
                        effect: "17,13,1",
                        effectRandom: true,
                        pauseTime: 2500,
                        transitionTime: 1000,
                        slices: 14,
                        boxes: 8,
                        hoverPause: 1,
                        autoAdvance: true,
                        captionOpacity: 0.3,
                        captionEffect: "fade",
                        thumbnailsWrapperId: "thumbs",
                        m: false,
                        license: "mylicense"
                    };

            var imageSlider = new mcImgSlider(sliderOptions);
        </script>

    </body>
</html>
