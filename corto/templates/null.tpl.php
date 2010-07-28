<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
        "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title>Null Auth</title>
    </head>
    <body>
        <p>Just click "OK" for standard authentication.</p>
        <form method="post" action="<?= $action ?>">
            <input type=hidden name=ID value="<?= $ID ?>">
            <input type=hidden name=pw value=abc>
            <input type=hidden name=uid value=abc>
            <input type=submit value="OK">
        </form>
    </body>
</html>