<?php
/**
 * ManiaLib - Lightweight PHP framework for Manialinks
 * 
 * @copyright   Copyright (c) 2009-2011 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 249 $:
 * @author      $Author: martin.gwendal@gmail.com $:
 * @date        $Date: 2011-08-12 13:41:42 +0200 (ven., 12 août 2011) $:
 */

namespace ManiaLib\Gui\Elements;

/**
 * FileEntry
 * File input field for Manialinks
 */
class FileEntry extends Entry
{
	/**#@+
	 * @ignore 
	 */
	protected $xmlTagName = 'fileentry';
	protected $folder;
	/**#@-*/
	
	/**
	 * Sets the default folder
	 * @param string
	 */
	function setFolder($folder)
	{
		$this->folder = $folder;
	}

	/**
	 * Returns the default folder
	 * @return string
	 */
	function getFolder()
	{
		return $this->folder;
	}

	/**
	 * @ignore 
	 */
	protected function postFilter()
	{
		parent::postFilter();
		if($this->folder !== null)
			$this->xml->setAttribute('folder', $this->folder);
	}
}

?>