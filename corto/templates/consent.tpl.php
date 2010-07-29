<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
        "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title>Discover ...</title>
    </head>
    <body>
        <form method="post" action="<?= $action ?>">
            <input type="hidden" name="ID" value="<?= $ID ?>" />
            <input type="hidden" name="consent" value="yes" />
            C: <?= $c ?>
            Vil du virkelig sende følgende:
            <pre>
            <? print_r($attributes) ?>
            </pre>
            <input type=submit value=Send>
        </form>
    </body>
</html>