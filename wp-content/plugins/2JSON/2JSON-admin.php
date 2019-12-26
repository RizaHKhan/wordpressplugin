<html>
<head>
    <style>
        p {
            width: 300px;
        }

        form {
            display: flex;
            flex-direction: column;
            width: 200px;
        }

        label {
            margin: 5px 0;
        }

        input {
            margin: 5px 0;
        }

        #button {
            border: solid 1px black;
            height: 32px;
            border-radius: 5px;
            transition: all .5s ease-in-out;
        }

        #button:hover {
            color: white;
            background: black;
            cursor: pointer;
        }
    </style>
</head>
    <body>
        <h1 class="header">2JSON</h1>
        <p>Start the download by pressing the button below. The file will retrieve your database information, convert it to json. To retrieve, please open your Downloads folder.</p>

        <form name="export" action="<?php echo plugin_dir_url(__FILE__) . 'export.php'?>" method="POST">
            <label for="website">Website Name:</label><input type="text" name="website">
            <input type="submit" id="button" value="Download" name="submit">
        </form>
    </body>
</html>