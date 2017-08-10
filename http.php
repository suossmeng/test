<?php
	class http{
		private $line = array();
		private $header = array();
		private $body = array();

		private $conn = null;
		private $version = "http/1.1";
		private $fh = null;

		private $errno = -1;
		private $errstr = '';
		const CRLF = "\r\n";
		private $response = '';


		function __construct($url){
			$this->conn($url);
			$this->setHeader("host" . $this->conn['host']);
		}
		function setLine($method){
			$this->line[0] = $method  . ' ' . $this->conn['path'] . '?' . $this->conn['query']  . ' ' . $this->version;
		}

		function setHeader($headerline){
			$this->header[] = $headerline;
		}
		function setBody($body){
			$this->body[] = http_build_query($body);
		}

		function conn($url){
			$this->conn = parse_url($url);
			if (!isset($this->conn['port'])) {
				$this->conn['port'] = 80;
			}
			if (!isset($this->conn['query'])) {
				$this->conn['query'] = '';
			}
			$this->fh = fsockopen($this->conn['host'],$this->conn['port'],$this->errno,$this->errstr,3);
		}

		function get(){
			$this->setLine('GET');
			$this->request();
			return $this->response;
		}

		function request(){
			$req = array_merge($this->line,$this->header,array(''),$this->body,array(''));
			$req = implode(CRLF, $req);
			fwrite($this->fh, $req);
			while (!feof($this->fh)) {
				$this->response .= fread($this->fh, 1024);
			}
			$this->close();
		}

		function post($body = array()){
			$this->setLine('POST');
			$this->setHeader('Content-type: application/x-www-form-urlencoded');
			$this->setBody($body);
			$this->setHeader('Content-length: ' . strlen($this->body[0]));
			$this->request();
			return $this->response;
		}

		function close(){
			fclose($this->fh);
		}
	}
