<?php
/**
 * This class is not opencart original mail class
 *
 * All the mail scene have been modified in this original opencart system
 *
 * Notice: the sender email host should be matched with the smtp host to avoid smtp host refusing
 * Specified the Sender before using SetFrom method in this case please
 * Reading SetFrom method to get more about Sender and Replyto proprities
 *
 * @author Sam@ozchamp.net
 * @example
 *

 */
require_once DIR_EXT.'/mailer.php';
class Mail extends Mailer{
	//
	public function __construct($exceptions = false){
		parent::__construct($exceptions);
	}
}
?>