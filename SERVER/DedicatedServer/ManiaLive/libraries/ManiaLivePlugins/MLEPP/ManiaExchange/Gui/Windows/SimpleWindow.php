<?php

namespace ManiaLivePlugins\MLEPP\ManiaExchange\Gui\Windows;

use ManiaLive\PluginHandler\PluginHandler;

use ManiaLib\Gui;
use ManiaLive\Gui\Windowing\Controls;
use ManiaLive\Gui\Windowing\Controls\Frame;
use ManiaLive\Gui\Windowing\Controls\Panel;
use ManiaLib\Gui\Elements\Label;
use ManiaLib\Gui\Elements\Quad;
use ManiaLib\Gui\Elements\Button;

class SimpleWindow extends \ManiaLive\Gui\Windowing\ManagedWindow
{
    protected $panel;
    protected $label;
    protected $Image;
    protected $MxID;
    protected $TrackName;
    protected $Username;
    protected $Uploaded;
    protected $Environment; 
    protected $Mood;
    protected $Style;
    protected $Routes;
    protected $Length;
    protected $Difficulty;
    protected $LBscore;
    protected $Awards;
    protected $data; 
	protected $mx;   
       
    function initializeComponents()
    {        
        $this->panel = new Panel();
        $this->panel->setPosition(2, 16);
        $this->addComponent($this->panel);
                    
        $this->frame = new Frame();
        $this->frame->setPosition(80, 60);
        $this->panel->addComponent($this->frame);

		$this->btnTrack = new Button();
        $this->frame->addComponent($this->btnTrack);
        $this->Image = new Quad();
        $this->frame->addComponent($this->Image);
        $this->MxID = new Label();
        $this->frame->addComponent($this->MxID);
        $this->TrackName= new Label();
        $this->panel->addComponent($this->TrackName);
        $this->Username= new Label();
        $this->panel->addComponent($this->Username);
        $this->Uploaded= new Label();
        $this->panel->addComponent($this->Uploaded);
        $this->Environment= new Label();
        $this->panel->addComponent($this->Environment);
        $this->Mood= new Label();
        $this->panel->addComponent($this->Mood);
        $this->Style= new Label();
        $this->panel->addComponent($this->Style);
        $this->Routes= new Label();
        $this->panel->addComponent($this->Routes);
        $this->Length= new Label();
        $this->panel->addComponent($this->Length);
        $this->Difficulty = new Label();
        $this->panel->addComponent($this->Difficulty);
        $this->Awards = new Label();
        $this->panel->addComponent($this->Awards);
        $this->URL = new Label();
        $this->panel->addComponent($this->URL);
        //$this->Image->setImage('http://'.$this->tmx.'.tm-exchange.com/get.aspx?action=trackscreen&id='. $this->data[0].'&.jpg', true);
        $this->Image->setImage('http://united.tm-exchange.com/get.aspx?action=trackscreen&id=3798990&.jpg', true); // placeholder        

    }
   
	function onDraw() {
        $routes = $this->parseRoutes($this->data->Routes);
        $length = $this->parseLength($this->data->Length);
        $difficulty = $this->parseDifficulty($this->data->Difficulty);
		$marginX = 6;
		$rowHeight = 6;
		$y = 12;
        $uploaded = strstr($this->data->UploadedAt, 'T', true);
    
    	$this->panel->clearComponents();
    	$this->setTitle('MX info for ' .$this->data->Name);
    	$this->MxID->setText('$000$oMX ID:               '.$this->data->TrackID.'');
    	$this->TrackName->setText('$000$oTrackname:                    '.$this->data->Name.'');
    	$this->Username->setText('$000$o'.$this->data->Name.'  $oMade By:  $o$l[http://'.$this->mx.'/user/profile/'.$this->data->UserID.']'.$this->data->Username.'$l');
    	$this->Uploaded->setText('$000$oUploaded:          '.$uploaded.'');
    	$this->Environment->setText('$000$oEnvironment:    '.$this->data->EnvironmentName.'');
    	$this->Mood->setText('$000$oMood:                '.$this->data->Mood.'');
    	$this->Style->setText('$000$oStyle:                 '.$this->data->StyleName.'');
    	$this->Routes->setText('$000$oRoutes:              '.$routes.'');
    	$this->Length->setText('$000$oLength:              '.$length.'');
    	$this->Difficulty->setText('$000$oDifficulty:           '.$difficulty.'');
    	$this->Awards->setText('$000$oAwards:             '.$this->data->AwardCount.'');
    	$this->btnTrack->setText('Visit '.$this->data->Name.' on MX');
    	$this->btnTrack->setUrl('http://'.$this->mx.'/tracks/view/'.$this->data->TrackID);
    	//$this->Image->setImage('http://'.$this->tmx.'.tm-exchange.com/get.aspx?action=trackscreen&id='. $this->data[0].'&.jpg', true);
    
    	$this->Username->setPosition(($this->getSizeX()/2), 2);
    	$this->Username->setSizeX($this->getSizeX()-8);
    	$this->Username->setSizeY(5);
    	$this->Username->setTextSize(3);
		$this->Username->setHalign("center");
    	$this->panel->addComponent($this->Username);
    	$this->MxID->setPosition($marginX, $y);
    	$this->MxID->setSizeX(60);
    	$this->MxID->setSizeY(4);
    	$this->MxID->setTextSize(2);
    	$this->panel->addComponent($this->MxID);
    	$this->Uploaded->setPosition($marginX, $y+=$rowHeight);
    	$this->Uploaded->setSizeX(60);
    	$this->Uploaded->setSizeY(4);
    	$this->Uploaded->setTextSize(2);
    	$this->panel->addComponent($this->Uploaded);
    	$this->Environment->setPosition($marginX, $y+=$rowHeight);
    	$this->Environment->setSizeX(60);
    	$this->Environment->setSizeY(4);
    	$this->Environment->setTextSize(2);
    	$this->panel->addComponent($this->Environment);
    	$this->Awards->setPosition($marginX, $y+=$rowHeight);
    	$this->Awards->setSizeX(60);
    	$this->Awards->setSizeY(4);
    	$this->Awards->setTextSize(2);
    	$this->panel->addComponent($this->Awards);
    	$this->Length->setPosition($marginX, $y+=$rowHeight);
    	$this->Length->setSizeX(60);
    	$this->Length->setSizeY(4);
    	$this->Length->setTextSize(2);
    	$this->panel->addComponent($this->Length);
    	$this->Style->setPosition($marginX, $y+=$rowHeight);
    	$this->Style->setSizeX(60);
    	$this->Style->setSizeY(4);
    	$this->Style->setTextSize(2);
    	$this->panel->addComponent($this->Style);
    	$this->Mood->setPosition($marginX, $y+=$rowHeight);
    	$this->Mood->setSizeX(60);
    	$this->Mood->setSizeY(4);
    	$this->Mood->setTextSize(2);
    	$this->panel->addComponent($this->Mood);
    	$this->Difficulty->setPosition($marginX, $y+=$rowHeight);
    	$this->Difficulty->setSizeX(60);
    	$this->Difficulty->setSizeY(4);
    	$this->Difficulty->setTextSize(2);
    	$this->panel->addComponent($this->Difficulty);
    	$this->Routes->setPosition($marginX, $y+=$rowHeight);
    	$this->Routes->setSizeX(60);
    	$this->Routes->setSizeY(4);
    	$this->Routes->setTextSize(2);
    	$this->panel->addComponent($this->Routes);
    	$this->Image->setPosition(80, 12);
    	$this->Image->setSizeX(120);
    	$this->Image->setSizeY(60);
    	$this->panel->addComponent($this->Image);
    	$this->btnTrack->setPosition(($this->getSizeX()/2), $y+=$rowHeight*2+3);
    	$this->btnTrack->setStyle(Button::CardButtonSmallWide);
		$this->btnTrack->setHalign("center");
		$this->btnTrack->setScale(1.5);
    	$this->panel->addComponent($this->btnTrack);
    }  
    
    function setData($data)
    {
        $this->data = $data;
    }
	
	function setTargetMx($mx) {
		$this->mx = $mx;
	}
	
    function destroy()
    {
        parent::destroy();
    }
    
    function parseLength($length) {
        switch($length) {
            case 0:
                $length = '15sec';
                break;
            case 1:
                $length = '30sec';
                break;
            case 2:
                $length = '45sec';
                break;
            case 3:
                $length = '1min';
                break;
            case 4:
                $length = '1min 15sec';
                break;
            case 5:
                $length = '1min 30sec';
                break;
            case 6:
                $length = '1min 45sec';
                break;
            case 7:
                $length = '2min';
                break;
            case 8:
                $length = '2min 30sec';
                break;
            case 9:
                $length = '3min';
                break;
            case 10:
                $length = '3min 30sec';
                break;
            case 11:
                $length = '4min';
                break;
            case 12:
                $length = '4min 30sec';
                break;
            case 13:
                $length = '5min';
                break;
            case 14:
                $length = 'Long';
                break;
        }
        
        return $length;
    }
    
    function parseDifficulty($difficulty) {
        switch($difficulty) {
            case 0:
                $difficulty = 'Beginner';
                break;
            case 1:
                $difficulty = 'Intermediate';
                break;
            case 2:
                $difficulty = 'Advanced';
                break;
            case 3:
                $difficulty = 'Extreme';
                break;
        }
        
        return $difficulty;
    }
    
    function parseRoutes($routes) {
        switch($routes) {
            case 0:
                $routes = 'Single';
                break;
            case 1:
                $routes = 'Multi';
                break;
            case 2:
                $routes = 'Symmetrical';
                break;
        }
        
        return $routes;
    }
}
?>
