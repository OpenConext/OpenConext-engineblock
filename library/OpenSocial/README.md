OpenSocial REST Client
===========
Simple client for OpenSocial JSON REST API.

Currently only supports Person and Group parts of 0.9 REST API spec.

Requirements
============
PHP > 5.2.0
Zend_Http

Examples
========
OpenSocial_Rest_Client::create($httpClient)->get('/person/{uid}/@all', array('uid'=>'john.doe'));