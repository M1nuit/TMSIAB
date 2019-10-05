<?php
/**
 * ManiaLive - TrackMania dedicated server manager in PHP
 * 
 * @copyright   Copyright (c) 2009-2011 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 249 $:
 * @author      $Author: martin.gwendal@gmail.com $:
 * @date        $Date: 2011-08-12 13:41:42 +0200 (ven., 12 août 2011) $:
 */

namespace ManiaLive\Gui\Windowing\Elements;

use ManiaLib\Gui\Manialink;

/**
 * Can be used to add already parsed xml to
 * a manialink window.
 * 
 * @author Florian Schnell
 */
class Xml extends \ManiaLive\Gui\Windowing\Element
{
	protected $xml;
	
	function __construct($xml = '')
	{
		$this->xml = $xml;
	}
	
	function setContent($xml)
	{
		$this->xml = $xml;
	}
	
	function save()
	{
		try
		{
			$doc = new \DOMDocument();
			$doc->loadXML($this->xml);
			$node = Manialink::$domDocument->importNode($doc->firstChild, true);
			end(Manialink::$parentNodes)->appendChild($node);
		}
		catch (\Exception $e)
		{
			$doc = new \DOMDocument();
			$doc->loadXML('<content>' . $this->xml . '</content>');
			foreach ($doc->firstChild->childNodes as $child)
			{
				$node = Manialink::$domDocument->importNode($child, true);
				end(Manialink::$parentNodes)->appendChild($node);
			}
		}
	}
}

?>