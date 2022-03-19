<?php

/*
Copyright (c) 2018, Florian Schaal - schaal @it UG
All rights reserved.

Redistribution and use in source and binary forms, with or without modification,
are permitted provided that the following conditions are met:

    * Redistributions of source code must retain the above copyright notice,
      this list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above copyright notice,
      this list of conditions and the following disclaimer in the documentation
      and/or other materials provided with the distribution.
    * Neither the name of ISPConfig nor the names of its contributors
      may be used to endorse or promote products derived from this software without
      specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY
OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE,
EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

class autoconfig {

    private $xml_version;
    private $xml_encoding;
    private $client_config_version;
    private $conf;
    private $mode;
    private $domain;
    private $email;
	private $debug = false;

    private $automail_settings;

    public function __construct($conf) {

        require_once('config.php');
        $this->conf = $conf;

        $this->xml_version = '1.0';
        $this->xml_encoding = 'UTF-8';
        $this->client_config_version = '1.1';

        $this->determineMode();
        switch ($this->mode) {
            case 'autodiscover':
                $this->prepareDataForAutoDiscover();
                break;
            case 'autoconfig':
                $this->prepareDataForAutoConfig();
                break;
            default:
                die('unknown mode');
        }
        $this->outputConfigurationXML();
    }

    private function prepareDataForAutoConfig() {
        $this->domain = explode('@', urldecode($_GET['emailaddress']))[1];
        if (empty($this->domain)) $this->displayDefaultPage();
    }

    private function prepareDataForAutoDiscover() {
        $data = file_get_contents("php://input");
        $this->writeToLogFile(print_r($data, true));
        if ($data != '') {
            preg_match("/\<EMailAddress\>(.*?)\<\/EMailAddress\>/", $data, $email);
            $this->email = $email[1];
            $this->domain = explode('@', $this->email)[1];
            if (empty($this->domain)) $this->displayDefaultPage();
        }
    }


    private function writeToLogFile($text) {
        if($this->debug) file_put_contents('output.log', $text, FILE_APPEND);
    }

    private function outputConfigurationXML() {
        $this->automail_settings = $this->getIspconfigAutomailSettings($this->domain);
        if (is_array($this->automail_settings)) {
            if ($this->mode == 'autoconfig') {
                $data = $this->autoconfig();
            } else {
                $data = $this->autodiscover();
            }
            $xml = new DOMDocument($this->xml_version, $this->xml_encoding);
            $child = $this->generate_xml_element($xml, $data);
            if ($child) $xml->appendChild($child);
            $xml->formatOutput = true;
            echo(header('content-type: text/xml'));
            echo $xml->saveXML($xml, LIBXML_NOEMPTYTAG);

            $this->writeToLogFile( $xml->saveXML($xml, LIBXML_NOEMPTYTAG) );
        } else {
            die('no data');
        }
    }

    private function displayDefaultPage() {
        readfile('index.html');
        exit;
    }

    private function determineMode() {
        $hostaddress = $_SERVER['HTTP_HOST'];
        $hostaddress = explode('.', $hostaddress);
        $this->mode = $hostaddress[0];
    }

    private function getIspconfigAutomailSettings($domain) {
        $error = '';
        $remote = new SoapClient(null,
            array(
                'location' => $this->conf['remote_uri'],
                'uri' => $this->conf['remote_uri'],
                'trace' => 1,
                'exceptions' => 1,
                'stream_context' => stream_context_create(
                    array(
                        'ssl' => array(
                            'verify_peer' => false,
                            'verify_peer_name' => false
                        )
                    )
                )
            )
        );

        try {
            $session_id = $remote->login($this->conf['remote_user'], $this->conf['remote_password']);
            $this->writeToLogFile( 'Session-ID for remote user: '.$session_id );
            $data = $remote->automail_get($session_id, $domain);
           	$this->writeToLogFile("\n\nautomail settings:\n");
            $this->writeToLogFile(print_r($data, true));
            $this->writeToLogFile("\n------------------------------\n\n");
        } catch (SoapFault $e) {
            $error .= print_r($e, true);
            $error .= $remote->__getLastResponse();
        }

        if ($error != '') $data = 'error';

        return $data;
    }

    private function write_server_autoconfig($protocol, $type = 'incomingServer') {
        $server = $this->automail_settings['in_hostname'];
        if ($this->automail_settings['use_ssl_in'] == 'y') {
            $ssl = 'STARTTLS';
            if ($this->automail_settings[$protocol . '_port'] == '993' || $this->automail_settings[$protocol . '_port'] == '995') $ssl = 'SSL';
        } else {
            $ssl = 'plain';
        }
        if ($type == 'outgoingServer') {
            $server = $this->automail_settings['out_hostname'];
            $ssl = @($this->automail_settings['use_ssl_out'] == 'y') ? 'STARTTLS' : 'plain';
        }
        $out = array('name' => $type, 'attributes' => array('type' => $protocol));
        $out[] = array('name' => 'hostname', 'value' => $server);
        $out[] = array('name' => 'port', 'value' => $this->automail_settings[$protocol . '_port']);
        $out[] = array('name' => 'socketType', 'value' => $ssl);
        $out[] = array('name' => 'authentication', 'value' => 'password-cleartext');
        $out[] = array('name' => 'username', 'value' => '%EMAILADDRESS%');

        return $out;
    }

    private function write_server_autodiscover($protocol, $type = 'incomingServer') {
        $server = $this->automail_settings['in_hostname'];
        $ssl = @($this->automail_settings['use_ssl_in'] == 'y') ? 'on' : 'off';

        $out = array('name' => 'Protocol');
        if ($type == 'outgoingServer') {
            $server = $this->automail_settings['out_hostname'];
            $ssl = @($this->automail_settings['use_ssl_out'] == 'y') ? 'on' : 'off';

            $out[]=array('name'=>'UsePOPAuth','value'=>'on');
            if ($this->automail_settings[$protocol . '_port'] == '587') {
                $out[] = array('name' => 'SSL', 'value' => 'off');
                $out[] = array('name' => 'Encryption', 'value' => 'TLS');
            } else {
                $out[] = array('name' => 'SSL', 'value' => $ssl);
            }
        } else {
            $out[] = array('name' => 'SSL', 'value' => $ssl);
        }

        $out[] = array('name' => 'Type', 'value' => strtoupper($protocol));
        $out[] = array('name' => 'LoginName', 'value' => $this->email);
        $out[] = array('name' => 'Server', 'value' => $server);
        $out[] = array('name' => 'Port', 'value' => $this->automail_settings[$protocol . '_port']);
        $out[] = array('name' => 'SPA', 'value' => 'on');
        $out[] = array('name' => 'AuthRequired', 'value' => 'on');
        $out[] = array('name'=>'DomainRequired','value'=>'on');

        return $out;
    }

    private function autoconfig() {
        $use_imap = @(intval($this->automail_settings['imap_port']) != 0 && $this->automail_settings['in_hostname'] != '') ? true : false;
        $use_pop3 = @(intval($this->automail_settings['pop3_port']) != 0 && $this->automail_settings['in_hostname'] != '') ? true : false;
        $use_smtp = @(intval($this->automail_settings['smtp_port']) != 0 && $this->automail_settings['out_hostname'] != '') ? true : false;

        $out = array(
            'name' => 'clientConfig', 'attributes' => array('version' => '1.1'),
            array(
                'name' => 'emailProvider', 'attributes' => array('id' => $this->automail_settings['email_provider']),
                array('name' => 'domain', 'value' => $this->domain),
                array('name' => 'displayName', 'value' => $this->automail_settings['display_name']),
                array('name' => 'displayShortName', 'value' => $this->automail_settings['email_provider'])
            )
        );

        if ($use_imap == true) $out[0][] = $this->write_server_autoconfig('imap');
        if ($use_pop3 == true) $out[0][] = $this->write_server_autoconfig('pop3');
        if ($use_smtp == true) $out[0][] = $this->write_server_autoconfig('smtp', 'outgoingServer');

        return $out;
    }

    private function autodiscover() {
        $use_imap = @(intval($this->automail_settings['imap_port']) != 0 && $this->automail_settings['in_hostname'] != '') ? true : false;
        $use_pop3 = @(intval($this->automail_settings['pop3_port']) != 0 && $this->automail_settings['in_hostname'] != '') ? true : false;
        $use_smtp = @(intval($this->automail_settings['smtp_port']) != 0 && $this->automail_settings['out_hostname'] != '') ? true : false;

        $out = array(
            'name' => 'Autodiscover', 'attributes' => array('xmlns' => 'http://schemas.microsoft.com/exchange/autodiscover/responseschema/2006'),
            array(
                'name' => 'Response', 'attributes' => array('xmlns' => 'http://schemas.microsoft.com/exchange/autodiscover/outlook/responseschema/2006a')
            )
        );


        $out[0][] = array('name' => 'Account');
        $out[0][0][] = array('name' => 'AccountType', 'value' => 'email');
        $out[0][0][] = array('name' => 'Action', 'value' => 'settings');
		/*
        $out[0][0][] = array('name' => 'Image', 'value' => 'https://systemschmiede.com/img/logo/ss.png');
        $out[0][0][] = array('name' => 'ServiceHome', 'value' => 'https://systemschmiede.com/');
		*/
        if ($use_imap == true) $out[0][0][] = $this->write_server_autodiscover('imap');
        if ($use_pop3 == true) $out[0][0][] = $this->write_server_autodiscover('pop3');
        if ($use_smtp == true) $out[0][0][] = $this->write_server_autodiscover('smtp', 'outgoingServer');

        return $out;
    }

    private function generate_xml_element($dom, $data) {
        if (empty($data['name'])) return false;
        $element_value = (!empty($data['value'])) ? $data['value'] : null;
        $element = $dom->createElement($data['name'], $element_value);
        if (!empty($data['attributes']) && is_array($data['attributes'])) {
            foreach ($data['attributes'] as $attribute_key => $attribute_value) {
                $element->setAttribute($attribute_key, $attribute_value);
            }
        }
        foreach ($data as $data_key => $child_data) {
            if (!is_numeric($data_key))
                continue;

            $child = $this->generate_xml_element($dom, $child_data);
            if ($child)
                $element->appendChild($child);
        }

        return $element;
    }
}
?>
