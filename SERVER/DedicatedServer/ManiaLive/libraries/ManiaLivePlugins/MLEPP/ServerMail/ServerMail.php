<?php

/**
 * MLEPP - ManiaLive Extending Plugin Pack
 *
 * -- MLEPP Plugin --
 * @name Server Mail
 * @date 31-01-2011
 * @version r1050
 * @website mlepp.trackmania.nl
 * @package MLEPP
 *
 * @author The MLEPP team
 * @copyright 2010 - 2011
 *
 * ---------------------------------------------------------------------
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 * You are allowed to change things or use this in other projects, as
 * long as you leave the information at the top (name, date, version,
 * website, package, author, copyright) and publish the code under
 * the GNU General Public License version 3.
 * ---------------------------------------------------------------------
 */



namespace ManiaLivePlugins\MLEPP\ServerMail;

use ManiaLive\DedicatedApi\Connection;
use ManiaLive\Features\Admin\AdminGroup;
use ManiaLive\Features\ChatCommand\Command;
use ManiaLive\Utilities\Console;
use ManiaLive\Data\Storage;
use ManiaLive\Config\Loader;
use ManiaLive\Event\Dispatcher;

use ManiaLivePlugins\MLEPP\Core\Core;
use ManiaLivePlugins\MLEPP\Core\Mlepp;
use ManiaLivePlugins\MLEPP\Database\Structures\multiQuery;
use ManiaLivePlugins\MLEPP\ServerMail\Gui\Windows\MailWindow;
use ManiaLivePlugins\MLEPP\ServerMail\Gui\Controls\MailItem;
use ManiaLivePlugins\MLEPP\ServerMail\Gui\Windows\ReadWindow;
use ManiaLib\Gui\Elements\Icons64x64_1;

class ServerMail extends \ManiaLive\PluginHandler\Plugin {
    protected $connected;

	private $token = array();
	private $message = array();
	private $target = array();
	protected $mlepp = null;
    //help
	private $descMail = "Usage: /mail  \$icheck|read|send\$i";

	private $helpMail = "This function provides a mail system.
You can send and receive mail, and it will be saved to the database.
This allows to communicate with players that are not online at the same time as you are.
It can also serve as memo function, as you can send yourself a mail.
The mail function also helps communicate with the server admins.

\$wUsage\$z:
\$o/mail check\$z - Check if you have received new mail.
\$o/mail read\$z - Open a window with your inbox.
\$o/mail send  \$ilogin\$i  text goes here\$z - Send a message to a login.
\$o/mail send admin text goes here\$z  - Send a mail to all admins.
";
	private $helpSendMail = "This function lets you send mail to other players, admins or yourself.
The mails will be saved in the database, making it possible to communicate with players that are not online at the same time as you are.
The mail function also helps communicate with the server admins.
For more info on the mail function type \$i/mail help\$z.

\$wUsage\$z:
\$o/mail send  \$ilogin\$i text goes here\$z - Send a message to a login.
\$o/mail send admin text goes here\$z  - Send a mail to all admins.";

    /**
     * onInit()
     * Function called on initialisation of ManiaLive.
     *
     * @return void
     */

	function onInit() {
	   $this->setVersion(1050);
       $this->setPublicMethod('getVersion');

        $this->mlepp = Mlepp::getInstance();

		//Oliverde8 Menu
		if($this->isPluginLoaded('oliverde8\HudMenu')) {
			Dispatcher::register(\ManiaLivePlugins\oliverde8\HudMenu\onOliverde8HudMenuReady::getClass(), $this);
		}
	}

    /**
     * onLoad()
     * Function called on loading of ManiaLive.
     *
     * @return void
     */

	function onLoad() {
        if($this->isPluginLoaded('MLEPP\Database', 200)) {
            Console::println('['.date('H:i:s').'] [MLEPP] Plugin: ServerMail r'.$this->getVersion() );
            $this->connected = true;
            $this->enableDedicatedEvents();
			$command = $this->registerChatCommand("mail", "smail", -1, true);
			$command->help = $this->descMail;
			if($this->isPluginLoaded('Standard\Menubar')) {
				$this->callPublicMethod('Standard\Menubar','initMenu', Icons64x64_1::NewMessage);
				$this->callPublicMethod('Standard\Menubar','addButton','Read Mail', array($this, 'menuReadMail'), false);
			}
		} else {
            Console::println('['.date('H:i:s').'] [MLEPP] [ServerMail] Plugin couldn\'t been load because plugin \'MLEPP\Database\' isn\'t activated.');
            $this->connected = false;
        }

	}

	 /**
	 * onOliverde8HudMenuReady()
     * Function used for adding buttons to Olivers Hud Menu.
	 *
	 * @param mixed $menu
	 * @return void
	 */
	public function onOliverde8HudMenuReady($menu) {
		$parent = $menu->findButton(array("Menu", "Basic Commands"));

		if(!$parent) {
			$button["style"] = "Icons64x64_1";
			$button["substyle"] = "GenericButton";
			$parent = $menu->addButton("Menu", "Basic Commands", $button);
		}

		$button["style"] = "Icons64x64_1";
		$button["substyle"] = "NewMessage";
		$button["plugin"] = $this;
		$button["function"] = "menuReadMail";
		$menu->addButton($parent, "Read Mail", $button);

	}


	 /**
	 * menuReadMail()
	 * Function providing the "read mail" menubutton.
     *
	 * @param mixed $login
	 * @param mixed $fromPlugin
	 * @return void
	 */

	function menuReadMail($login, $fromPlugin = NULL) {
        $this->readMail($login);
	}

	 /**
	 * onReady()
     * Function called when ManiaLive is ready loading.
	 *
	 * @return void
	 */

	function onReady() {
        if($this->connected == true) {
            /*$dbinfo = $this->callPublicMethod('MLEPP\Database', 'getConnection');
            $this->mlepp_db = $dbinfo['connection'];
            $this->mlepp->db->type = $dbinfo['dbtype']; */
            $this->initDatabaseTables();
        }
	}

	 /**
	 * onPlayerConnect()
     * Function called when a player connects.
	 *
	 * @param mixed $login
	 * @param mixed $isSpectator
	 * @return void
	 */

	function onPlayerConnect($login, $isSpec) {
        //$this->mlepp->sendChat('%servermail%This server has server mail. type /mail help for more info.',$login);
	 	$this->checkMail($login);
	}

	 /**
	 * checkMail()
     * Function providing the chat message ("... new messages").
	 *
	 * @param mixed $login
	 * @return void
	 */

	function checkMail($login) {
        $q  = "SELECT * FROM `servermail` WHERE `mail_to` = ".$this->mlepp->db->quote($login)." AND mail_isread = 'false';";
        $dbData = $this->mlepp->db->query($q);
        $newMessages = $dbData->recordCount();
        $this->mlepp->sendChat('%servermail%You have %variable%'.$newMessages.' %servermail% new message(s), see /mail for more info.', $login);
        $this->console('['.$login.'] '.$newMessages.' new message(s)');
	}

	 /**
	 * smail()
     * Function providing the /smail command.
	 *
	 * @param mixed $login
	 * @param mixed $action
	 * @param mixed $target
	 * @param mixed $message
	 * @return void
	 */

	function smail($login, $action = NULL, $target= NULL, $message = NULL) {
		$args = func_get_args();
		$login = array_shift($args);
		$action = array_shift($args);
		$target = array_shift($args);
		$message = implode(" ",$args);

		$player = $this->storage->getPlayerObject($login);
        $this->console('['.$login.'] Player used /smail '.$action.'.');
		switch($action) {
				case 'send':
							$this->sendMail($login,$target,$message);
							break;
				case 'read':
							$this->readMail($login);
							break;
				case 'token':
							$this->validateToken($login,$target);
							break;
				case 'check':
							$this->checkMail($login);
							break;
				case 'help':
							$this->showHelp($login,$this->helpMail);
							break;
				default:
							$this->showHelp($login,$this->helpMail);
							break;

		}
	}

	function validateAdminMail($login,$target,$message = NULL) {
		if(!$message || $message == "help") {
			$this->mlepp->sendChat("%servermail%Usage: /mail admin text goes here",$login);
			return;
		}

		if($this->mlepp->AdminGroup->hasPermission($login,'admin')) {
			$this->sendAdminMessage($login, $target, $message);
			return;
		}

		$unique = md5( uniqid() );
		$unique = substr($unique,0,12);
		$unique = str_split($unique,3);
		$this->token[$login] = implode("-",$unique);
		$this->message[$login] = $message;
		$this->target[$login] = $target;
		$this->mlepp->sendChat("%servermail%To send message to all admins, type /mail token ".$this->token[$login], $login);
		$this->showHelp($login,"To send message to all admins\n type:\n /mail token ".$this->token[$login]);
	}

	 /**
	 * validateToken()
     * Function that validates the mailtoken.
	 *
	 * @param mixed $login
	 * @param mixed $token
	 * @return
	 */

	function validateToken($login, $token = NULL) {
		if(!$token) {
				$this->mlepp->sendChat("%servermail%Usage: /mail token xxx-xxx-xxx-xxx",$login);
				return;
			}

		if(!isset($this->token[$login])) {
				$this->mlepp->sendChat("%servermail%You don't have valid open tokens.",$login);
				return;
		}

		if($this->token[$login] == $token) {

			if($this->target[$login] == "admin") {
				$this->sendAdminMessage($login, "admin", $this->message[$login]);
			} else {
				$this->sendAdminMessage($login, $this->target[$login], $this->message[$login]);
			}

			unset($this->token[$login]);
			unset($this->message[$login]);
			unset($this->target[$login]);

        } else {
			$this->mlepp->sendChat("%servermail%Invalid token entered.", $login);
			$this->mlepp->sendChat("%servermail%To send message, type /mail token ".$this->token[$login], $login);
        }
	}

	function sendAdminMessage($login, $target, $message) {
	 	$admins = $this->mlepp->AdminGroup->getAdmins();
        if($target == "admin") {
            foreach($admins as $target2) {
                $this->addMailToDb($login,$target2,$message);
            }
            $this->mlepp->sendChat("%servermail%Mail sent to all admins on server.",$login);
            $this->console('['.$login.'] Send mail to all admins: '.$message.'.');
        } else {
            $this->addMailToDb($login,$target,$message);
            $this->mlepp->sendChat("%servermail%Mail sent to admin $target on server.",$login);
            $this->console('['.$login.'] Send mail to admin ('.$target.'): '.$message.'.');
        }
	}

	 /**
	 * sendMail()
     * Function used for sending mail.
	 *
	 * @param mixed $login
	 * @param mixed $target
	 * @param mixed $message
	 * @return
	 */

	function sendMail($login, $target = NULL, $message = NULL) {
        if(!$target && !$message) {
			$this->showHelp($login, $this->helpSendMail);
			return;
		}

		if($target == "help") {
			$this->showHelp($login, $this->helpSendMail);
			return;
		}

		if(!$message)  {
			$this->mlepp->sendChat("%servermail%Usage: /mail send $target \text goes here",$login);
			return;
		}

		if($target == "admin") {
			$this->validateAdminMail($login,"admin",$message);
			return;
		}

		if($this->checkPlayerLogin($target)) {
			if($this->mlepp->AdminGroup->hasPermission($login,'admin') ) {
				$this->validateAdminMail($login,$target,$message);
				return;
			}
			if($this->addMailToDb($login,$target,$message)) $this->mlepp->sendChat("%servermail%Servermail sent Successfully.",$login);
			else $this->mlepp->sendChat("%servermail%Error sending servermail.");
		} else {
			$this->mlepp->sendChat("%servermail%Login $target has not visited the server. You can't send mail to an unknown login.",$login);
		}
	}

	 /**
	 * showHelp()
	 * Function used for showing the help window.
     *
	 * @param mixed $login
	 * @param mixed $text
	 * @return void
	 */

	function showHelp($login,$text) {
		$this->callPublicMethod('MLEPP\Core', 'showHelp', $login, "help for plugin ".$this->getName(), $text);
	}

	 /**
	 * initDatabaseTables()
     * Function providing the ServerMail database,
     * called on initializing of ManiaLive.
	 *
	 * @return
	 */

    private function initDatabaseTables() {
        if(!$this->mlepp->db->tableExists('servermail')){
		        $q = "CREATE TABLE `servermail` (
                               	`mail_id` MEDIUMINT( 9 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                                `mail_from` VARCHAR( 30 ) NOT NULL,
                                `mail_to` 	 VARCHAR( 30 ) NOT NULL,
								`mail_text` VARCHAR( 255 ) NOT NULL,
                                `mail_isread` BOOL DEFAULT FALSE,
                                `mail_date` DATETIME
                            ) CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE = MYISAM ;";

        $this->mlepp->db->query($q);
        $this->console("Created servermail table successfully.");
        $this->mlepp->db->setDatabaseVersion('servermail',1);
		}
    }

	 /**
	 * checkPlayerLogin()
     * Function checking the player login.
	 *
	 * @param mixed $target
	 * @return
	 */

	function checkPlayerLogin($target) {
        $q = "SELECT * FROM `players` WHERE `player_login` = ".$this->mlepp->db->quote($target).";";
        $dbData = $this->mlepp->db->query($q);

        if($dbData->recordCount() == 1) {
            return true;
        }
		return false;
	}

	 /**
	 * setMessageRead()
     * Helper function, marks message as read.
	 *
	 * @param mixed $login
	 * @param mixed $id
	 * @return void
	 */

	function setMessageRead($login,$id) {
			$q = "UPDATE
			`servermail`
			SET
			`mail_isread` = true
			WHERE
			`mail_id` = ".$this->mlepp->db->quote($id).";";

			$this->mlepp->db->query($q);
			//$this->mlepp->sendChat("%servermail%Message set to read.",$login);
	}

	 /**
	 * deleteMessage()
     * Helper function, deletes a message.
	 *
	 * @param mixed $login
	 * @param mixed $id
	 * @return void
	 */

	function deleteMessage($login,$id) {
			$q = "DELETE FROM
			`servermail`
			WHERE
			`mail_id` = ".$this->mlepp->db->quote($id).";";

			$this->mlepp->db->query($q);
			$this->mlepp->sendChat("%servermail%Message deleted.",$login);
			$this->readMail($login);
	}

	 /**
	 * addMailToDb()
     * Helper function, adds message to the database.
	 *
	 * @param mixed $login
	 * @param mixed $to
	 * @param mixed $text
	 * @return
	 */

	function addMailToDb($login,$to,$text) {
	 		$q = "INSERT INTO `servermail` (`mail_from`,
                                                    `mail_to`,
                                                    `mail_text`,
                                                    `mail_isread`,
                                                    `mail_date`
                                                   ) VALUES (
                                                    ".$this->mlepp->db->quote($login).",
                                                    ".$this->mlepp->db->quote($to).",
                                                    ".$this->mlepp->db->quote($text).",
                                                    'false',
                                                    ".$this->mlepp->db->quote(date('Y-m-d H:i:s'))."
                                                   )";

		    $this->mlepp->db->query($q);
			if ($this->mlepp->db->affectedRows() != 1) return false;
			return true;

	}



	 /**
	 * console()
     * Helper function, addes MLEPP messages.
	 *
	 * @param mixed $text
	 * @return void
	 */

	function console($text) {
		Console::println('['.date('H:i:s').'] [MLEPP] [ServerMail] '.$text);
	}

	 /**
	 * readMail()
     * Function provides the read mail window.
	 *
	 * @param mixed $login
	 * @return void
	 */

	function readMail($login) {
	  	$window = MailWindow::Create($login);
		$window->setSize(180, 100);
		$window->clearAll();
		// prepare cols ...
		$window->addColumn('isRead', 0.1);
		$window->addColumn('From', 0.2);
		$window->addColumn('NickName', 0.3);
		$window->addColumn('Date', 0.2);
		$window->addColumn('Show', 0.1);
		$window->addColumn('Delete', 0.1);

		// refresh records for this window ...
		$window->clearItems();

		$q = "SELECT * FROM `servermail` LEFT JOIN `players` ON (`servermail`.`mail_from` = `players`.`player_login`)
		WHERE `mail_to` = ".$this->mlepp->db->quote($login)."
        ORDER BY `mail_date` ASC;";


		$query = $this->mlepp->db->query($q);

		while($mail[] = $query->fetchStdObject());

		foreach($mail as $m) {
			if(empty($m)) break;

				if($m->mail_isread == true) $read = "Yes"; else $read = "No";
				$entry = array
				(
					'isRead' => array($read,$m->mail_id,true),
					'From' =>  array($m->mail_from,NULL,false),
					'NickName' =>  array($m->player_nickname,NULL,false),
					'Date' =>  array($m->mail_date,NULL,false),
					'Show' =>  array("Show",$m->mail_id,true),
					'Delete' => array("Delete",$m->mail_id,true)
				);
				$window->addMailItem($entry, array($this, 'onClick'));
		}

		// display or update window ...
		$window->centerOnScreen();
		$window->show();
	}

	 /**
	 * showMessage()
     * Function showing the message.
	 *
	 * @param mixed $login
	 * @param mixed $id
	 * @return
	 */

	function showMessage($login,$id) {
		$this->setMessageRead($login,$id);
	  	$window = ReadWindow::Create($login);
		$window->setSize(180, 100);
		$q = "SELECT * FROM `servermail` LEFT JOIN `players` ON (`servermail`.`mail_from` = `players`.`player_login`)
        WHERE `mail_id` = ".$this->mlepp->db->quote($id).";";

		$query = $this->mlepp->db->query($q);

		if($query->recordCount() == 0) {
			return false;
		} else {
			$m = $query->fetchStdObject();
		}

		$window->setTopic("Message from ".$m->player_nickname);
		$window->setText($m->mail_text);

		// display or update window ...
		$window->centerOnScreen();
		$window->show();
	}


	 /**
	 * onClick()
     * Function called on clicking.
	 *
	 * @param mixed $login
	 * @param mixed $action
	 * @param mixed $target
	 * @return void
	 */

	function onClick($login, $action, $target = NULL) {
		switch ($action) {
			case 'isRead':
				$this->setMessageRead($login, $target);
				break;
			case 'Delete':
				$this->deleteMessage($login, $target);
				break;
			case 'Show':
				$this->showMessage($login, $target);
				break;
			case 'readMail':
				$this->readMail($login);
				break;
		}
	}
}
?>