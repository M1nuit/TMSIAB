<?php 
namespace ManiaLivePlugins\MLEPP\Karma\Gui\Controls;

use ManiaLib\Gui\Elements\BgsPlayerCard;
use ManiaLib\Gui\Elements\Bgs1InRace;
use ManiaLib\Gui\Elements\Icons64x64_1;
use ManiaLib\Gui\Elements\Icons128x128_1;
use ManiaLib\Gui\Elements\BgRaceScore2;
use ManiaLib\Gui\Elements\Quad;
use ManiaLib\Gui\Elements\Label;

class Star extends \ManiaLive\Gui\Windowing\Control
{
	public $callBack;
	public $active;
	public $highlite;
	protected $starValue;
	private $gfxStar;
	protected $gfxBackground;
	protected $login;
	
	function initializeComponents()
	{
		$this->starValue = $this->getParam(0);
		$this->highlite = $this->getParam(1);
		$this->login = $this->getParam(2);
		$this->sizeY = 2;
		$this->sizeX = 2;
		//$this->gfxBackground = new Quad();
		//$this->addComponent($this->gfxBackground);
		
		$this->gfxStar = new Quad();
		$this->gfxStar->setPosZ(-101);
		$this->gfxStar->setValign("center");
		$this->addComponent($this->gfxStar);
	}
	
	function beforeDraw()
	{
		$sizeBig = 2.5;
		$sizeSmall =2;
		
		if ($this->active == true) {
			$this->sizeY = $sizeBig;
			$this->sizeX = $sizeBig;
			//$this->gfxBackground->setSize($sizeBig,$sizeBig);
			$this->gfxStar->setImage("http://koti.mbnet.fi/reaby/manialive/images/star.png",true);
			$this->gfxStar->setImageFocus("http://koti.mbnet.fi/reaby/manialive/images/star_hover.png",true);
			$this->gfxStar->setSize($sizeBig,$sizeBig);
		}
		else {
			$this->sizeY = $sizeSmall;
			$this->sizeX = $sizeSmall;
			$this->gfxStar->setImage("http://koti.mbnet.fi/reaby/manialive/images/star.png",true);
			$this->gfxStar->setImageFocus("http://koti.mbnet.fi/reaby/manialive/images/star_hover.png",true);
			$this->gfxStar->setSize($sizeSmall,$sizeSmall);
		}
		if ($this->highlite == true) {
			$this->gfxStar->setImage("http://koti.mbnet.fi/reaby/manialive/images/star_active.png",true);
		}
/*		else {
			$this->gfxBackground->setStyle(BgsPlayerCard::BgsPlayerCard);
			$this->gfxBackground->setSubStyle(BgsPlayerCard::BgPlayerCardBig);
		}*/
	
		$this->gfxStar->setAction($this->callback('onClicked'));
		$this->gfxStar->setVisibility(true);
		$this->gfxStar->setPosZ(-101);	
	}
	
	function onResize()
	{
	}
	
	function afterDraw() {}

	function onClicked($login)
	{
		call_user_func($this->callBack, $login, $this->starValue);
		//$this->redraw();
	} 
	
	function destroy()
	{
		$this->callBack = null;
		parent::destroy();
	}
}
?>