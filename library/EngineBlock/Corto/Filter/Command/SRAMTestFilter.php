<?php

/**
 * Copyright 2021 Stichting Kennisnet
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

class EngineBlock_Corto_Filter_Command_SRAMTestFilter extends EngineBlock_Corto_Filter_Command_Abstract
    implements EngineBlock_Corto_Filter_Command_ResponseAttributesModificationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getResponseAttributes()
    {
        return $this->_responseAttributes;
    }

    public function execute(): void
    {
        error_log("SRAMTestFilter execute(" . $this->_string. ")");

        $attributes = $this->getResponseAttributes();

        $uid = $attributes['urn:mace:dir:attribute-def:uid'][0];
        $id = $this->_request->getId();

        $headers = array(
            'Authorization: Test'
        );

        $post = array(
            'uid' => $uid,
            'id' => $id
        );

        $options = [
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $post,
        ];

        $ch = curl_init("http://192.168.0.1:12345/ping");
        curl_setopt_array($ch, $options);

        $data = curl_exec($ch);
        curl_close($ch);

        $body = json_decode($data);
        $msg = $body->msg;

        if ('pong' == $msg) {
            error_log("SRAMTestFilter PONG!");
            $this->_response->setSRAMInterrupt(true);
        }

    }
}
