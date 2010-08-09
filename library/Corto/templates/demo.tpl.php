<html>
    <head>
        <style type="text/css" title="text/css">
        <!--
        body
        {
            color: #CC9933;
            background-color: #FFFFCC;
            font-size: large;
            font-family: verdana,monospace;
            text-align: left;
        }

        pre
        {
            font-size: medium;
        }

        table
        {
            padding: 1ex;
        }

        td.r
        {
            text-align: right;
            vertical-align: top;
        }

        div
        {
            border: 1px dotted;
            margin: 1em;
            padding: 1em;
            width: 55em;
        }

        a:link, a:visited, a:hover, a:active
        {
            text-decoration: none;
            color: #CC9933;
        }
-->
        </style>
    </head>
    <body>
        <img alt="" src="images/corto_logo_606x223.png" width="303" height="111" /><br />
        <?= $message ?>
        <form method=POST action="<?= $action ?>">
            <input name=doit value=1 type="hidden" />
            <? foreach($idps as $idp): ?>
            <input type=checkbox name="IDPList[]" value="<?= $idp ?>"><?= $idp?> <br>
            <? endforeach; ?>
            <input type=checkbox name="idp" value="wayf_vidp1" xchecked>vidp1 via wayf<br>
            <input type=checkbox name="idp" value="wayf_idp1" checked>Idp1 via wayf<br>
            <input type=checkbox name="idp" value="wayf_idp2">Idp2 via wayf<br>
            <input type=checkbox name="idp" value="idp1">Idp1 directly<br>
            <input type=checkbox name=IDPList[]  value="http://jach-idp.test.wayf.dk/saml2/idp/metadata.php">jach-idp<br>
            <input type=checkbox name=IDPList[]  value="https://pure.wayf.ruc.dk/myWayf">pure-idp<br>
            <p>
                <input type=checkbox name=ForceAuthn value=true>ForceAuthn
                <input type=checkbox name=IsPassive value=true>IsPassive
                <a href="<?=CORTO_BASE_URL . '/idp1/sendUnsolicitedAssertion'?>">Send unsolicited assertion (from idp1)</a><br>
                <a href="<?= $self . "/wayf/shibSingleSignOnService?shire=" . urlencode($self) . "/main/demoapp&providerId=" . urlencode($self) . "/main" ?>">Shibboleth</a>
            </p>
            <input type=submit value="Send Request">
        </form>

        <pre>
        <?= preg_replace("/\n\n/", "\n", preg_replace("/Array\n\s+/", "Array ", htmlspecialchars(print_r($hSAMLResponse, 1)))); ?>
        </pre>
    </body>
</html>