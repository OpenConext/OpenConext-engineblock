<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>Identity Provider Selectie - SURFmedia</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta name="robots" content="noindex, nofollow"/>
        <link href="https://espee.surfnet.nl/federate/surfnet/css/screen.css" rel="stylesheet" type="text/css" />
        <link href="https://espee.surfnet.nl/federate/surfnet/css/table.css" rel="stylesheet" type="text/css" />
    </head>
    <body>

        <div class="wrapper">
            <div class="header">
                <a href="http://www.surfnet.nl">
                    <img class="logo" src="https://espee.surfnet.nl/federate/surfnet/img/surfnet_logo.gif" alt="logo" />
                </a>
                <span>SURFnet Service Provider</span>
                <span class="left"></span>
                <span class="right"></span>
                <img src="https://espee.surfnet.nl/federate/surfnet/img/federatie_header.jpg" alt="header img" />
            </div>
            <div class="main">
                <div class="column content">
                    <div class="item">
                        <span class="h2">Attribuut vrijgave</span>

                        <form method="post" action="<?= $action ?>">
                            <input type=hidden name=ID value="<?= $ID ?>">

                            <p>
                                Wil je de volgende attributen vrijgeven voor de doel applicatie?
                            </p>

                            <?php foreach ($attributes as $attributename => $attributevalue) { ?>
                            <dl style="margin-left: 60px;">
                                <dt style="font-weight: bold;"><?=$attributename?></dt>
                                <dd>
                                    <ul>
                                    <?php foreach ($attributevalue as $value) { ?>
                                        <li><?=$value?></li>
                                    <?php } ?>
                                    </ul>
                                </dd>
                            </dl>
                            <? } ?>
                            <input type=submit value="Bevestig">
                        </form>
                    </div>
                </div>
            </div>
            <div class="footer">
                <p>Bezoek ook:</p>
                <ul>
                    <li><a href="http://www.surfnet.nl">SURFnet</a></li>
                </ul>
                <address>
                    <span><strong>SURFnet bv</strong></span>
                    <span>Radboudkwartier 273</span>
                    <span>Postbus 19035</span>
                    <span>3501 DA Utrecht</span>
                    <span>T +31 302 305 305</span>
                    <span>F +31 302 305 329</span>
                    <a href="mailto:help@surfmedia.nl">
                        help@surfmedia.nl
                    </a>
                    <a class="extra" href="http://www.surfnet.nl/nl/pages/copyright.aspx">
                        Copyright
                    </a>
                    <a class="extra" href="http://www.surfnet.nl/nl/pages/disclaimer.aspx">
                        Disclaimer
                    </a>
                </address>
            </div>
        </div>
    </body>
</html>