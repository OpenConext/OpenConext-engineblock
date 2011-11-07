<?php

class EngineBlock_Shindig_Response_EmptyResponseItem extends ResponseItem
{
    public function getResponse()
    {
        return array('entry'=>array());
    }
}