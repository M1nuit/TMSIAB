<?php
/**
 * Represents a Dedicated TrackMania Server Challenge
 * ManiaLive - TrackMania dedicated server manager in PHP
 * 
 * @copyright   Copyright (c) 2009-2011 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 249 $:
 * @author      $Author: martin.gwendal@gmail.com $:
 * @date        $Date: 2011-08-12 13:41:42 +0200 (ven., 12 août 2011) $:
 */
namespace ManiaLive\DedicatedApi\Structures;

class Challenge extends AbstractStructure
{
	public $uId;
	public $name;
	public $fileName;
	public $author;
	public $environnement;
	public $mood;
	public $bronzeTime;
	public $silverTime;
	public $goldTime;
	public $authorTime;
	public $copperPrice;
	public $lapRace;
	public $nbLaps;
	public $nbCheckpoints;
}