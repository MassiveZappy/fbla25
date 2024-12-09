<!DOCTYPE html>
<html>
    <?php include "header.php"; ?>
    <head>
        <title>Home</title>
        <link rel="stylesheet" type="text/css" href="style.css">
        <meta name="viewport" content="height=100px, initial-scale=1.0" />
    </head>
    <body>
        <h2>Home</h2>
        <section>
            <p class="centered" id="PHStitle">Penncrest Financial Tracker</p>
            <img class="mySlides" src="https://images.pexels.com/photos/4270292/pexels-photo-4270292.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2" style="width:100%">
            <img class="mySlides" src="https://images.pexels.com/photos/771881/pexels-photo-771881.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2" style="width:100%">
            <img class="mySlides" src="https://images.pexels.com/photos/2422588/pexels-photo-2422588.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2" style="width:100%">
        </section>

        <script>
            // Automatic Slideshow - change image every 5 seconds
            var myIndex = 0;
            carousel();

            function carousel() {
                var i;
                var x = document.getElementsByClassName("mySlides");
                for (i = 0; i < x.length; i++) {
                    x[i].style.display = "none";
                }
                myIndex++;
                if (myIndex > x.length) {myIndex = 1}
                x[myIndex-1].style.display = "block";
                setTimeout(carousel, 5000);
            }
        </script>
    </body>
    <?php include "footer.php"; ?>
</html>
