<html>
    <head>
        <style>
            .container {
                display: flex;
                flex-direction: column;
            }

            .header {
                border: solid 5px black;
                padding: 1em 2em;
                width: 300px;
                text-align: center;
                border-radius: 5px;
                background: 0;
            }

            .mainForm {
                width: 410px;
                display: flex;
                flex-direction: column;
            }

            label {
                padding: 5px;
                width: 410px;
                display: flex;
                margin: 5px 0;
                border-bottom: 1px solid rgba(0,0,0,.2);
            }

            input {
                margin: 0 0 0 auto;
            }

            button {
                width: 160px;
                padding: .5em 1.5em;
                margin: 5px 0 auto auto;
                background: black;
                border-radius: 5px;
                border: solid 1px black;
                color: white;
                font-size: 15px;
            }

            button:hover {
                background: #F1F1F1;
                color: black;

            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1 class="header">2JSON</h1>
            <p class="subheader">Converting adRotate SQL tables to JSON</p>
            <form method="POST" class="mainForm">
                <label>Host:<input type="text" name="host"></label>
                <label>Database Name:<input type="text" name="db"></label>
                <label>User Name:<input type="text" name="user"></label>
                <label>Password:<input type="password" name="password"></label>
                <label>Prefix:<input type="text" name="prefix" value="wp_"></label>
                <button name="test">Start Conversion</button>
            </form>
            <?php 
                if(isset($_POST["test"])) {
                    require_once dirname(__FILE__) . '/adrotate/adrotate-json.php';
                }
            ?>
        </div>
    </body>
</html>