<?php

/**
 * MLEPP - ManiaLive Extending Plugin Pack
 *
 * -- MLEPP Core --
 * @name Core
 * @date 02-07-2011
 * @version r934 TM2 Beta
 * @website mlepp.trackmania.nl
 * @package MLEPP
 *
 * @author The MLEPP team
 * @copyright 2010 - 2011
 *
 * ---------------------------------------------------------------------
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License; or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful;
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not; see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 * You are allowed to change things or use this in other projects; as
 * long as you leave the information at the top (name; date; version;
 * website; package; author; copyright) and publish the code under
 * the GNU General Public License version 3.
 * ---------------------------------------------------------------------
 */

namespace ManiaLivePlugins\MLEPP\Core;

class Config extends \ManiaLib\Utils\Singleton {

	public $joinPlayer = '%welcome%Welcome %variable%%nickname%$z$s%welcome%, this server is running %variable%MLEPP r%version%%welcome%!';
	public $replacepos = 0;
	public $Colors_emote = '$z$s$ea0$i';
	public $Colors_server = '$ae0';
	public $Colors_welcome = '$ea0';
	public $Colors_error = '$f00';
	public $Colors_adminerror = '$f44';
	public $Colors_adminaction = '$0ae';
	public $Colors_variable = '$fff';
	public $Colors_mail = '$faf';
	public $Colors_rank = '$ff0';
	public $Colors_atm = '$fc0';
	public $Colors_donate = '$e0a';
	public $Colors_music = '$ea0';
	public $Colors_karma = '$fc0';
	public $Colors_jukebox = '$ea0';
	public $Colors_record = '$0f0';
	public $Colors_winner = '$ccc';
	public $Colors_idlekick = '$06f';
	public $Colors_idlemsg = '$ff0';
	public $Colors_vote = '$afa';
	public $checkpoints = true;
	public $notices = false;
}

?>