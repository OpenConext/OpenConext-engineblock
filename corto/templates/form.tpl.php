<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <title>POST data</title>
    </head>
    <body <? if (!$trace): ?>onload="document.forms[0].submit()"<? endif; ?>>
        <noscript>
            <p>
                <strong>Note:</strong>
                Since your browser does not support JavaScript, you must press the button below once to proceed.
            </p>
        </noscript>
        <form method="post" action=" <?= $action ?>">
            <input type="hidden" name="<?= $name ?>" value="<?= $message ?>" />

            <?= $xtra ?>

            <noscript><input type="submit" value="Submit" /></noscript>

            <? if ($trace): ?>
            <input type="submit" value="Submit" />
            <pre>
                <?= $trace ?>
            </pre>
            <? endif; ?>

        </form>
    </body>
</html>