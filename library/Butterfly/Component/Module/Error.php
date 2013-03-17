<?php

class Butterfly_Component_Module_Error extends Butterfly_Component_Module
{
	public function notfoundAction()
	{
		header('HTTP/1.0 404 Not Found');
	}
}
