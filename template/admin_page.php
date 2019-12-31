<html>
<head>
    <style>
        
    </style>
</head>
    <body>
        <h1 class="header">2JSON</h1>
        <p>Start the download by pressing the button below. The file will retrieve your database information, convert it to json. To retrieve, please open your Downloads folder.</p>

        <form name="export" action="<?php echo plugin_dir_url(__FILE__) . '../export.php'?>" method="POST">
            <label for="website">Website Name:</label><input type="text" name="website">
            <input type="submit" id="button" value="Download" name="submit">
        </form>
    </body>
</html>