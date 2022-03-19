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


class remoting_automail extends remoting {

	public function automail_get($session_id, $domain) {
		global $app;

		if(!$this->checkPerm($session_id, 'automail')) {
			throw new SoapFault('permission_denied', 'You do not have the permissions to access this function.');
			return false;
		}

		$data = $app->db->queryOneRecord("SELECT email_provider, display_name, in_hostname, out_hostname, imap_port, pop3_port, smtp_port, use_ssl_in, use_ssl_out FROM mail_config WHERE server_id = (SELECT server_id FROM mail_domain WHERE domain = ?) AND active = 'y'", $domain);
	
		if(is_array($data) && !empty($data)) {
		} else {
			$data = 'no data found';
		}

		return $data;
	}
    
	public function automail_add($session_id, $params) {
		global $app;

		if(!$this->checkPerm($session_id, 'automail')) {
			throw new SoapFault('permission_denied', 'You do not have the permissions to access this function.');
			return false;
		}

        return $this->insertQuery('../automail/form/automail.tform.php', $params);
	}

}

?>
