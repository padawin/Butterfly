<?php


class Butterfly_Exception extends Exception
{

    public function __construct($message)
    {
        //$this->_sendMail();
        parent::__construct($message);
    }


    private function _sendMail()
    {
        //-----------------------------------------------
        //DECLARE LES VARIABLES
        //-----------------------------------------------

        $sender = 'exception@ghislain-rodrigues.fr';
        $reply = 'contact@ghislain-rodrigues.fr';
        $messageText = $this->getMessage();
        $messageHtml='<html>
        <head>
        <title>Exception</title>
        </head>
        <body>' . $this->getMessage() . '</body>
        </html>';
        //-----------------------------------------------
        //GENERE LA FRONTIERE DU MAIL ENTRE TEXTE ET HTML
        //-----------------------------------------------

        $frontiere = '-----=' . md5(uniqid(mt_rand()));

        //-----------------------------------------------
        //HEADERS DU MAIL
        //-----------------------------------------------

        $headers = 'From: "Exception ghislain-rodrigues.fr" <'.$sender.'>'."\n";
        $headers .= 'Return-Path: <'.$reply.'>'."\n";
        $headers .= 'MIME-Version: 1.0'."\n";
        $headers .= 'Content-Type: multipart/mixed; boundary="'.$frontiere.'"';

        //-----------------------------------------------
        //MESSAGE TEXTE
        //-----------------------------------------------
        $message = 'This is a multi-part message in MIME format.'."\n\n";

        $message .= '--'.$frontiere."\n";
        $message .= 'Content-Type: text/plain; charset="iso-8859-1"'."\n";
        $message .= 'Content-Transfer-Encoding: 8bit'."\n\n";
        $message .= $messageText . "\n\n";

        //-----------------------------------------------
        //MESSAGE HTML
        //-----------------------------------------------
        $message .= '--'.$frontiere."\n";

        $message .= 'Content-Type: text/html; charset="iso-8859-1"'."\n";
        $message .= 'Content-Transfer-Encoding: 8bit'."\n\n";
        $message .= $messageHtml."\n\n";

        $message .= '--'.$frontiere.'--'."\n";

        //-----------------------------------------------
        //PIECE JOINTE
        //-----------------------------------------------

        $message .= 'Content-Type: text/plain; name="$_SERVER.txt"'."\n";
        $message .= 'Content-Transfer-Encoding: 8bit'."\n";
        $message .= 'Content-Disposition:attachement; filename="$_SERVER.txt"'."\n\n";

        $message .= var_export($_SERVER, true)."\n";
        $message .= 'Content-Type: text/plain; name="exception.txt"'."\n";
        $message .= 'Content-Transfer-Encoding: 8bit'."\n";
        $message .= 'Content-Disposition:attachement; filename="$exception.txt"'."\n\n";

        $message .= var_export($this->getTrace(), true)."\n";

        mail('exception@ghislain-rodrigues.fr', 'exception', $message, $headers);
    }

}
