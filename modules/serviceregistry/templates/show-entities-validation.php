<?php
/**
 * SURFconext Service Registry
 *
 * LICENSE
 *
 * Copyright 2011 SURFnet bv, The Netherlands
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and limitations under the License.
 *
 * @category  SURFconext Service Registry
 * @package
 * @copyright Copyright © 2010-2011 SURFnet SURFnet bv, The Netherlands (http://www.surfnet.nl)
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

$this->data['jquery'] = array('version' => '1.6', 'core' => TRUE, 'ui' => TRUE, 'css' => TRUE);
$this->data['head']   = '
<style type="text/css">
.header-25 {
    font-weight: bold;
    text-decoration: underline;
    font-size: small;
}

.entity .messages p {
    margin:  0px;
    padding: 5px;
    text-align: center;
    font-weight: bold;
}

.entity .messages .error {
    background-color: #CD5C5C;
    color: white;
}

.entity .messages .warning {
    background-color: #F0E68C;
}
img.certificate, img.certificate-chain {
    float:left;
}
img.certificate-chain {
    margin-top: 25px;
}

div.certificate-image-container {
    text-decoration:none;
    position:relative;
    float:left;
}

.selfsigned-overlay {
    background-color: white;
    font-weight: bold;
    left: 16%;
    opacity: 0.5;
    position: absolute;
    top: 48%;
}
.rootca-overlay {
    background-color: white;
    font-weight: bold;
    left: 13%;
    opacity: 0.5;
    position: absolute;
    top: 20%;
}

</style>
';
$this->includeAtTemplateBase('includes/header.php');
?>

<div id="tabdiv">
    <p style="font-size: large; text-align: center;">
        <a href="<?php echo SimpleSAML_Module::getModuleURL('janus/index.php'); ?>">Go back to the JANUS Dashboard</a>
    </p>
    <hr />

    <ul>
        <?php foreach ($this->data['entities'] as $type => $entities): ?>
        <li class="entity-type">
            <h1><?php
                if ($type=='saml20-sp') {
                    echo "Service Providers";
                } else if ($type==='saml20-idp') {
                    echo "Identity Providers";
                } else {
                    echo $type;
                }?></h1>
            <ul>
                <?php foreach ($entities as $entity): ?>
                <li class="entity">
                    <h2 name="<?php echo $entity['Id']; ?>">
                        <?php echo $entity['Name']; ?>
                    </h2>

                    <div class="entity-messages messages">
                    </div>

                    <script class="messages-template" type="text/x-jquery-tmpl">
                        {{each Errors}}
                        <p class="error">${$value}</p>
                        {{/each}}
                        {{each Warnings}}
                        <p class="warning">${$value}</p>
                        {{/each}}
                    </script>

                    <table class="entity-information">
                        <tr>
                            <th>Entity ID</th>
                            <td>
                                <a href="<?php echo $entity['Id'] ?>" class="entity-id">
                                    <?php echo $entity['Id'] ?>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>Metadata URL</th>
                            <td>
                                <a href="<?php echo $entity['MetadataUrl'] ?>">
                                    <?php echo $entity['MetadataUrl'] ?>
                                </a>
                            </td>
                        </tr>
                    </table>

                    <br />

                    <p class="header-25">
                        Signing Certificate
                    </p>

                    <div class="entity-certificate-representation">
                    </div>

                    <div class="entity-certificate-information">
                        <img class="loading-image" alt='Loading...' src="resources/images/icons/spinner.gif" />
                    </div>

                    <script class="entity-certificate-information-template" type="text/x-jquery-tmpl">
                        <table>
                            <tr>
                                <th>Subject:</th>
                                <td>${Subject}</td>
                            </tr>
                            <tr>
                                <th>Starts / started:</th>
                                <td>${Starts_natural} (${Starts_relative})</td>
                            </tr>
                            <tr>
                                <th>Ends / ended:</th>
                                <td>${Ends_natural} (${Ends_relative})</td>
                            </tr>
                        </table>
                    </script>

                    <br />

                    <p class="header-25">
                        Endpoints
                    </p>
                    <img class="loading-image" alt='Loading...' src="resources/images/icons/spinner.gif" />
                    <ul class="entity-endpoints">
                    </ul>

                    <script class="entity-endpoint-template" type="text/x-jquery-tmpl">
                        <li>
                            <h3>
                                <img style="display: inline;" height="24px" width="24px" src="resources/images/icons/endpoint.png" alt="" />
                                ${Name}
                            </h3>
                            <a href="${Url}">${Url}</a>

                            <div class="entity-endpoint-messages messages">
                            </div>

                            <div class="entity-endpoint-certificate-representation">
                            </div>

                            <div class="entity-endpoint-certificate-information">
                            </div>
                        </li>
                    </script>
                </li>
                <?php endforeach; ?>
            </ul>
        </li>
        <?php endforeach; ?>
    </ul>
    <hr />
    <p style="font-size: large; text-align: center;">
        <a href="<?php echo SimpleSAML_Module::getModuleURL('janus/index.php'); ?>">Go back to the JANUS Dashboard</a>
    </p>
</div>
<script type="text/javascript" src="resources/scripts/datehelper.js"></script>
<script type="text/javascript" src="resources/scripts/jquery.tmpl.min.js"></script>
<script type="text/javascript" src="resources/scripts/serviceregistry.validate.js"></script>
<?php
$this->includeAtTemplateBase('includes/footer.php');