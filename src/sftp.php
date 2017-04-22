<?php
namespace bmtmgr\sftp;

class SFTPPublication extends \bmtmgr\Publication {
	protected static function table_name() {
		return 'publication';
	}

	public static function sftp_create($tournament, $server, $port, $path, $username) {
		list($priv_key, $pub_key, $passphrase) = create_keypair();

		$config = [
			'server' => $server,
			'port' => $port,
			'path' => $path,
			'username' => $username,
			'priv_key' => $priv_key,
			'pub_key' => $pub_key,
			'pasphrase' => $passphrase,
		];
		return new static([
			'id' => null,
			'tournament_id' => $tournament->id,
			'ptype' => 'sftp',
			'config' => \json_encode($config)
		]);
	}

	public function sftp_get_config() {
		return \json_decode($this->config, true);
	}

	public function sftp_get_server() {
		$config = $this->sftp_get_config();
		return $config['server'];
	}

	public function sftp_get_port() {
		$config = $this->sftp_get_config();
		return $config['port'];
	}

	public function sftp_get_path() {
		$config = $this->sftp_get_config();
		return $config['path'];
	}

	public function sftp_get_username() {
		$config = $this->sftp_get_config();
		return $config['username'];
	}

	public function sftp_get_priv_key() {
		$config = $this->sftp_get_config();
		return $config['priv_key'];
	}

	public function sftp_get_pub_key() {
		$config = $this->sftp_get_config();
		return $config['pub_key'];
	}

	public function sftp_get_passphrase() {
		$config = $this->sftp_get_config();
		return $config['pasphrase'];
	}

	public function configuration_str() {
		return $this->sftp_get_server() . ':' . $this->sftp_get_path();
	}

	public function publish() {
		$priv_fn = \tempnam(\sys_get_temp_dir(), 'bmtmgr_sftp_upload_key');
		$pub_fn = $priv_fn . '.pub';
		$script_fn = $priv_fn . '.script';
		$content_fn = $priv_fn . '.content';
		$cmd = ('sftp ' .
			' -P ' . \escapeshellarg($this->sftp_get_port()) .
			' -o IdentitiesOnly=yes -F /dev/null ' .
			' -o StrictHostKeyChecking=no ' . // TODO: not a good idea in production
			'-i ' . \escapeshellarg($priv_fn) . ' ' .
			'-b' . \escapeshellarg($script_fn) . ' ' .
			\escapeshellarg($this->sftp_get_username()) . '@' . \escapeshellarg($this->sftp_get_server())
		);

		$sftp_script = 'put ' . \escapeshellarg($content_fn) . ' ' . \escapeshellarg($this->sftp_get_path());
		\file_put_contents($content_fn, 'TODO: HTML GOES HERE5');
		\file_put_contents($script_fn, $sftp_script);
		\file_put_contents($pub_fn, $this->sftp_get_pub_key());
		\file_put_contents($priv_fn, $this->sftp_get_priv_key());

		$descriptorspec = array(
			0 => array('pipe', 'r'),
			1 => array('pipe', 'w'),
			2 => array('pipe', 'w'),
		);
		try {
			$proc = \proc_open($cmd, $descriptorspec, $pipes);
			if (!$proc) {
				throw new \Exception('failed to start sftp');
			}

			\fwrite($pipes[0], $this->sftp_get_passphrase() . "\n");
			\fclose($pipes[0]);

			$stdout = \stream_get_contents($pipes[1]);
			\fclose($pipes[1]);

			$stderr = \stream_get_contents($pipes[2]);
			\fclose($pipes[2]);
			
			$return_value = \proc_close($proc);

			die('stdout: ' . $stdout . ', stderr: ' . $stderr . ' return: ' . $return_value);
		} finally {
			/*\unlink($priv_fn);
			\unlink($pub_fn);
			\unlink($script_fn);
			\unlink($content_fn);*/	
		}
		die("");
	}
}

function create_keypair() {
	$passphrase = \bmtmgr\utils\gen_token();
	$tmpname = \tempnam(\sys_get_temp_dir(), 'bmtmgr_sftp_key');
	\unlink($tmpname);

	\exec(
		'ssh-keygen -q -t rsa -N ' . \escapeshellarg($passphrase) . ' -f ' . \escapeshellarg($tmpname),
		$output, $return_val);
	if ($return_val != 0) {
		throw new \Exception('SSH key creation failed: ' . \implode("\n", $output));
	}
	$priv_key = \file_get_contents($tmpname);
	\unlink($tmpname);
	$pub_key = \file_get_contents($tmpname . '.pub');
	\unlink($tmpname . '.pub');

	return [$priv_key, $pub_key, $passphrase];
}

function encode_public_key($priv_key) {
    $keyInfo = openssl_pkey_get_details($priv_key);
    $buffer  = pack("N", 7) . "ssh-rsa" .
    _encode_buffer($keyInfo['rsa']['e']) . 
    _encode_buffer($keyInfo['rsa']['n']);
    return "ssh-rsa " . base64_encode($buffer);
}

function _encode_buffer($buffer) {
    $len = strlen($buffer);
    if (ord($buffer[0]) & 0x80) {
        $len++;
        $buffer = "\x00" . $buffer;
    }
    return pack("Na*", $len, $buffer);
}
