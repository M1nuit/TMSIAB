<?php
/**
* remoteCP 4
* ütf-8 release
*
* @package remoteCPlive
* @author |Black|Co2NTRA and |Black|Dennis23
* @copyright (c) 2006-2010
* @version 4.0.3.5 v1.5
*/
class Adminpanel extends rcp_liveplugin
{
	public    $title		= 'Adminpanel';
	public    $author		= '|Black|Co2NTRA & Dennis23';
	public    $version		= '4.0.3.5 v1.5';
	public    $vpermissions	= array('editmaps', 'editplayers');
	private	  $messages 	= array();

	
	public function onLoad()
	{
		Core::getObject('chat')->addCommand('ap', 'onMLAAdminpanelChangeexp', 'Minimize the adminpanel in score or race mode', '/ap', 'editmaps');
	}

	public function onNewPlayer($player)
	{
		$player->cdata[$this->id]['exp'] = true;
		if(Core::getObject('live')->checkPerm('editplayers', $player->Login) || Core::getObject('live')->checkPerm('editmaps', $player->Login)) {
			$window = $player->ManiaFWK->addWindow('MLContainerAdminpanel', ' ', -52.5, -27, 20);
			if($window) {
				$window->setOption('header', false);
				$window->setOption('static', true);
				$window->setOption('close', false);
				$window->setOption('bg', false);
			}
		}
	}
	
	public function onPlayerConnect($params)
	{
		if(Core::getObject('live')->isAdmin($params[0]->Login,true)) return; {
		Core::getObject('manialink')->updateContainer('Adminpanel',$params[0]->Login);
		}
	}
	
	public function onBeginRace()
	{
		Core::getObject('manialink')->updateContainer('Adminpanel');
	}

	public function onEndRace()
	{
		Core::getObject('manialink')->updateContainer('Adminpanel');
	}

	public function onMLContainerAdminpanel($params)
	{
		$window = $params[1];
		$window->Reset();
		$window->setOption('posy', Core::getObject('status')->gamestate ? -27 : -42);
		$window->setOption('posx', Core::getObject('status')->gamestate ? -52.5 : 13);
		$window->Line();
		$window->Cell('Adminpanel', '100%','onMLAAdminpanelChangeexp',array('textsize' => '0.90', 'valign' => 'bottom'));
		if($params[0]->cdata[$this->id]['exp']){
			$window->Line();
			if(Core::getObject('live')->checkPerm('editmaps', $params[0]->Login)){
				$window->Cell('Last Map', array(7,2), 'onAdminLast', array('class' => 'btn1n', 'halign' => 'center'));
				$window->Cell('Next Map', array(7,2), 'onAdminNext', array('class' => 'btn1n', 'halign' => 'center'));
				$window->Cell('Restart Map', array(7,2), 'onAdminRestart', array('class' => 'btn1n', 'halign' => 'center'));
				$window->Cell('Force End', array(7,2), 'onAdminForceEnd', array('class' => 'btn1n', 'halign' => 'center'));
			}
			if(Core::getObject('live')->checkPerm('editplayers', $params[0]->Login)){
				$window->Cell('Cancel Vote', array(7,2), 'onAdminCancelVote', array('class' => 'btn1n', 'halign' => 'center'));
			}
		}
	}

	public function onMLAAdminpanelChangeexp($params)
	{
		$params[0]->cdata[$this->id]['exp'] = ($params[0]->cdata[$this->id]['exp']) ? false : true;
		Core::getObject('manialink')->updateContainer('Adminpanel', $params[0]);
	}
	
	public function onLoadSettings($settings)
	{
		$this->messages = array();
		$this->messages['restart']		= (string) $settings->messages->restart;
		$this->messages['next']			= (string) $settings->messages->next;
		$this->messages['forceend']		= (string) $settings->messages->forceend;
		$this->messages['cancelvote']	= (string) $settings->messages->cancelvote;
		$this->messages['lastmap']		= (string) $settings->messages->lastmap;
		$this->messages['warn']			= (string) $settings->messages->warn;
		$this->messages['kick']			= (string) $settings->messages->kick;
		$this->messages['ban']			= (string) $settings->messages->ban;
		$this->messages['ignore']		= (string) $settings->messages->ignore;
		$this->messages['unignore']		= (string) $settings->messages->unignore;
	}
	
	public function onAdminRestart($params)
	{
		if(!Core::getObject('live')->isAdmin($params[0]->Login)) return; 
		{
			$admins = Core::getObject('live')->getAdmins();
			Core::getObject('chat')->send(sprintf($this->messages['restart'], $this->getGroupName($admins[$params[0]->Login]->group), $params[0]->NickName));
		}
	}
	
	public function onAdminNext($params)
	{
		if(!Core::getObject('live')->isAdmin($params[0]->Login)) return;
		{
			$admins = Core::getObject('live')->getAdmins();
			Core::getObject('chat')->send(sprintf($this->messages['next'], $this->getGroupName($admins[$params[0]->Login]->group), $params[0]->NickName)); 
		}
	}
	
	public function onAdminForceEnd($params)
	{
		if(!Core::getObject('live')->isAdmin($params[0]->Login)) return;
		{
			$admins = Core::getObject('live')->getAdmins();
			Core::getObject('chat')->send(sprintf($this->messages['forceend'], $this->getGroupName($admins[$params[0]->Login]->group), $params[0]->NickName)); 
		}
	}
	
	public function onAdminCancelVote($params)
	{
		if(!Core::getObject('live')->isAdmin($params[0]->Login)) return;
		{
			$admins = Core::getObject('live')->getAdmins();
			Core::getObject('chat')->send(sprintf($this->messages['cancelvote'], $this->getGroupName($admins[$params[0]->Login]->group), $params[0]->NickName));
			}
	}
	
	public function onAdminLast($params)
	{
		if(!Core::getObject('live')->isAdmin($params[0]->Login)) return;
		{
			$admins = Core::getObject('live')->getAdmins();
			Core::getObject('chat')->send(sprintf($this->messages['lastmap'], $this->getGroupName($admins[$params[0]->Login]->group), $params[0]->NickName));
		}
	}
	
	public function Warn($params)
	{
		if(!Core::getObject('live')->isAdmin($params[0]->Login)) return;
		{
			$player = Core::getObject('players')->get($params[1]);
			if(!Core::getObject('players')->check($player)) return;
			
			$admins = Core::getObject('live')->getAdmins();
			Core::getObject('chat')->send(sprintf($this->messages['warn'], $this->getGroupName($admins[$params[0]->Login]->group), $params[0]->NickName, $player->NickName));
		}
	}
	
	public function Kick($params)
	{
		if(!Core::getObject('live')->isAdmin($params[0]->Login)) return;
		{
			$player = Core::getObject('players')->get($params[1]);
			if(!Core::getObject('players')->check($player)) return;
			$admins = Core::getObject('live')->getAdmins();
			Core::getObject('chat')->send(sprintf($this->messages['kick'], $this->getGroupName($admins[$params[0]->Login]->group), $params[0]->NickName, $player->NickName));
		}
	}
	
	public function Ban($params)
	{
		if(!Core::getObject('live')->isAdmin($params[0]->Login)) return;
		{
			$player = Core::getObject('players')->get($params[1]);
			if(!Core::getObject('players')->check($player)) return;
			
			$admins = Core::getObject('live')->getAdmins();
			Core::getObject('chat')->send(sprintf($this->messages['ban'], $this->getGroupName($admins[$params[0]->Login]->group), $params[0]->NickName, $player->NickName));
		}
	}
	
	public function Ignore($params)
	{
		if(!Core::getObject('live')->isAdmin($params[0]->Login)) return;
		{
			$player = Core::getObject('players')->get($params[1]);
			if(!Core::getObject('players')->check($player)) return;
			$msg = ($player->Ignored) ? $this->messages['ignore'] : $this->messages['unignore'];

			
			$admins = Core::getObject('live')->getAdmins();
			Core::getObject('chat')->send(sprintf($msg, $this->getGroupName($admins[$params[0]->Login]->group), $params[0]->NickName, $player->NickName));
		}
	}

	private function getGroupName($groupid)
	{
		//Get the group name
		$group = Core::getObject('session')->groups->xpath("/groups/group[id='{$groupid}']");
		if($group[0]) {
			return (string) $group[0]->name;
		}
		return 'invalid group';
	}
}
?>