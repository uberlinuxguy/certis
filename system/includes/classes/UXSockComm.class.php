<?
class UXSockComm extends Certis {

	var $sockHandle = FALSE;
	var $socketListening = FALSE;
	var $currSocket = FALSE;
	var $mode = "client";


	function openSocket() {
		// uses STREAM and TCP for the underlying communications
		$this->sockHandle = socket_create(AF_UNIX, SOCK_STREAM, 0); 

		if($this->sockHandle === FALSE || $this->sockHandle === NULL) {
			return FALSE;
		}

		// now bind the socket to the tmp socket the ExecDaemon will listen/connect via
		if($this->mode == "server") {
			$result = socket_bind($this->sockHandle, self::$config->execDaemonSocket);
		} else {
			$result = TRUE;
		}
		
		return $result;
	}


	function listen() {
		// if we haven't already been opened, let's try to open the socket.
		$this->mode = "server";
		if($this->sockHandle === FALSE) {
			if(!$this->openSocket()) {
				return FALSE;
			}
		}

		// ok, if we get *here* things should be ok, so set up the listen.
		$this->socketListening = socket_listen($this->sockHandle, SOMAXCONN);
		
		// set the socket to 777 so anyone can write to it.
		chmod(self::$config->execDaemonSocket, 0777);
		return $this->socketListening;

	}

	function acceptConn() {
		// let's try to do everything we need to do before we call accept
		// in other words, create/bind/listen on a socket if it's not already been
		// done for us.
		if($this->socketListening === FALSE) {
			// calling this->listen should stack all the neccessary steps for us.
			if(!$this->listen()) {
				return FALSE;
			}
		}

		// now that we are here, we should have a valid socket in listen state, so 
		// call accept which we will want to block.
		socket_set_block($this->sockHandle);

		$result = socket_accept($this->sockHandle);
		
		// exec daemon is meant to be serial, so set this->currSocket and return
		// so the caller can process the currSocket
		$this->currSocket = $result;

		// I know we are returning the currSocket to the caller, but that's ok
		// because I want to allow the possibliity of letting the caller process 
		// the socket should he/she want to.  As long as they call this->closeCurr()
		// things should be ok.
		return $result;
	}

	function clientConnect() {
		// make sure we are all set up to connect.
		if($this->sockHandle === FALSE) {
			if(!$this->openSocket()) {
				return FALSE;
			}
		}

		// things look ok, so let's connect and set this->currSocket
		$result = socket_connect($this->sockHandle, self::$config->execDaemonSocket);
		if($result === FALSE) { 
			return FALSE;
		}

		// for clients, the currSocket is the previously allocated sockHandle
		$this->currSocket = $this->sockHandle;

		
		return $result;
	}

	function readPacket() {

		// if the current Socket is disconnected, return FALSE to the caller.
		// because that means they did something bad
		if($this->currSocket === FALSE) {
			return FALSE;
		}

		// read in the packet
		$buffer="";
		do {
			$recv = "";
			$recv=socket_read($this->currSocket, 1024);
			if($recv===FALSE) {
				return FALSE;
			} elseif ($recv != "" ) {
				$buffer .= $recv;
			}
		} while ($recv = "");

		return $buffer;
	}


	function sendPacket($data) {
		// check that $this->currSocket is set.
		if($this->currSocket === FALSE) { 
			return FALSE;
		}

		// since socket_write doesn't necessarily write all bytes in a given buffer, 
		// we use a wacky loop here.  If in the midst of sending, we get an error, return
		// false and assume that no data was sent.  This is safe because readPacket() above 
		// will do the same thing.
		$len = strlen($data);
		$offset=0;
		while($offset < $len) {
			$sent = socket_write($this->currSocket, substr($data,$offset), $len-$offset);
			if($sent === FALSE) {
				return FALSE;
			}
			$offset += $sent;
		}

		// if we get here, then we can 'safely' assume that our 
		// packet has been sent.  We will return the size in the form
		// of $offset (which should equal $len) for the caller to verify.

		return $offset;
	}

	function close() {
		// closes the currSocket.
		if($this->currSocket !== FALSE) {
			socket_close($this->currSocket);
			$this->currSocket = FALSE;
			// if this was a client connection, let's reset the sockHandle too.
			if($this->mode == "client") {
				$this->sockHandle = FALSE;
			}
		}
	}
	
	function shutdown() {
		// first close the currSocket
		$this->close();
		
		
		//used to shut down a listening server.
		if($this->mode == "client") {
			// if we are called on a client instance, just do nothing else and return
			return;
		} else {
			
			// then close the sockHandle
			socket_close($this->sockHandle);
			$this->sockHandle = FALSE;
			
			// and reset our state to not listening.
			$this->socketListening = FALSE;
			
			// clean up the socket file
			unlink(self::$config->execDaemonSocket);
			
		}
	}

}
