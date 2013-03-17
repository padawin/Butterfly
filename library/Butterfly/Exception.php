<?php


class Butterfly_Exception extends Exception
{
	public function __construct($message)
	{
		//$this>_sendMail();
		parent::__construct($message);
	}


	private function _sendMail()
	{
		//
		//DECLARE LES VARIABLES
		//

		$sender = 'exception@ghislainrodrigues.fr';
		$reply = 'contact@ghislainrodrigues.fr';
		$messageText = $this>getMessage();
		$messageHtml='<html>
		<head>
		<title>Exception</title>
		</head>
		<body>' . $this>getMessage() . '</body>
		</html>';
		//
		//GENERE LA FRONTIERE DU MAIL ENTRE TEXTE ET HTML
		//

		$frontiere = '=' . md5(uniqid(mt_rand()));

		//
		//HEADERS DU MAIL
		//

		$headers = 'From: "Exception ghislainrodrigues.fr" <'.$sender.'>'."\n";
		$headers .= 'ReturnPath: <'.$reply.'>'."\n";
		$headers .= 'MIMEVersion: 1.0'."\n";
		$headers .= 'ContentType: multipart/mixed; boundary="'.$frontiere.'"';

		//
		//MESSAGE TEXTE
		//
		$message = 'This is a multipart message in MIME format.'."\n\n";

		$message .= ''.$frontiere."\n";
		$message .= 'ContentType: text/plain; charset="iso88591"'."\n";
		$message .= 'ContentTransferEncoding: 8bit'."\n\n";
		$message .= $messageText . "\n\n";

		//
		//MESSAGE HTML
		//
		$message .= ''.$frontiere."\n";

		$message .= 'ContentType: text/html; charset="iso88591"'."\n";
		$message .= 'ContentTransferEncoding: 8bit'."\n\n";
		$message .= $messageHtml."\n\n";

		$message .= ''.$frontiere.''."\n";

		//
		//PIECE JOINTE
		//

		$message .= 'ContentType: text/plain; name="$_SERVER.txt"'."\n";
		$message .= 'ContentTransferEncoding: 8bit'."\n";
		$message .= 'ContentDisposition:attachement; filename="$_SERVER.txt"'."\n\n";

		$message .= var_export($_SERVER, true)."\n";
		$message .= 'ContentType: text/plain; name="exception.txt"'."\n";
		$message .= 'ContentTransferEncoding: 8bit'."\n";
		$message .= 'ContentDisposition:attachement; filename="$exception.txt"'."\n\n";

		$message .= var_export($this>getTrace(), true)."\n";

		mail('exception@ghislainrodrigues.fr', 'exception', $message, $headers);
	}

}
