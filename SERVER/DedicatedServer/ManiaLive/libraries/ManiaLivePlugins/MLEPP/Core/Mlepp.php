<?php

/**
 * MLEPP - ManiaLive Extending Plugin Pack
 *
 * -- MLEPP Class --
 * @name Mlepp
 * @date 21-06-2011
 * @version r934
 * @website mlepp.trackmania.nl
 * @package MLEPP - Core
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

namespace ManiaLivePlugins\MLEPP\Core;

use ManiaLive\Utilities\Console;
use ManiaLive\DedicatedApi\Connection;
use ManiaLive\Data\Storage;
use ManiaLivePlugins\MLEPP\Core\Core;
use ManiaLivePlugins\MLEPP\Core\Config;
use ManiaLivePlugins\MLEPP\Core\Profiles;
use ManiaLivePlugins\MLEPP\Core\AdminGroups;

class Mlepp extends \ManiaLib\Utils\Singleton {

	private $connection;
	private $storage;
	public $config;
	public $AdminGroup;
	public $gameVersion;
	public $db;

	protected function __construct() {
		$this->storage = Storage::getInstance();
		$this->connection = Connection::getInstance();
		$this->gameVersion = $this->connection->getVersion();
		$this->config = Config::getInstance();
		$this->AdminGroup = AdminGroups::getInstance();
		$this->AdminGroup->load();
	}

	static function getInstance() {
		return parent::getInstance();
	}

	public function Depot($group) {
		return new \ManiaLivePlugins\MLEPP\Core\Structures\DepotGroup($group);
	}
	
	 /**
	 * sendChat()
	 * Function used for sending text to the server.
	 *
	 * @param mixed $text
	 * @param string $login
	 * @return void
	 */
	public function sendChat($text, $login = false) {
		try {
			if ($login) {
				if (isset($login->login)) {
					$loginObj = $login;
				} else {
					$loginObj = $this->storage->getPlayerObject($login);
				}
				$this->connection->chatSendServerMessage('$fff» ' . $this->parseColors($text), $loginObj, true);
			} else {
				$this->connection->chatSendServerMessage('$fff»» ' . $this->parseColors($text));

			}
		}
		catch (\Exception $e) {
			Console::println('[MLEPP] A General error occurred when sending message to game:'.$e->getMessage()."\n".$text);
		}
	}

	public function log($text) {
		$out = \ManiaLib\Utils\TMStrings::stripAllTmStyle($text);
		Console::println("[".Console::getDatestamp()."] [MLEPP] [CHAT] ".$out);
	}
	 /**
	 * parseColors()
	 * Function use for parsing the MLEPP colors.
	 *
	 * @param mixed $text
	 * @return
	 */
	public function parseColors($text) {
		$message = $text;
		$message = str_replace('%emote%', $this->config->Colors_emote, $message);
		$message = str_replace('%server%', $this->config->Colors_server, $message);
		$message = str_replace('%welcome%', $this->config->Colors_welcome, $message);
		$message = str_replace('%error%', $this->config->Colors_error, $message);
		$message = str_replace('%adminerror%', $this->config->Colors_adminerror, $message);
		$message = str_replace('%adminaction%', $this->config->Colors_adminaction, $message);
		$message = str_replace('%variable%', $this->config->Colors_variable, $message);
		$message = str_replace('%servermail%', $this->config->Colors_mail, $message);
		$message = str_replace('%rank%', $this->config->Colors_rank, $message);
		$message = str_replace('%autotrackmanager%', $this->config->Colors_atm, $message);
		$message = str_replace('%donate%', $this->config->Colors_donate, $message);
		$message = str_replace('%music%', $this->config->Colors_music, $message);
		$message = str_replace('%karma%', $this->config->Colors_karma, $message);
		$message = str_replace('%jukebox%', $this->config->Colors_jukebox, $message);
		$message = str_replace('%recordcolor%', $this->config->Colors_record, $message);
		$message = str_replace('%winnercolor%', $this->config->Colors_winner, $message);
		$message = str_replace('%idlekickcolor%', $this->config->Colors_idlekick, $message);
		$message = str_replace('%idlemsgcolor%', $this->config->Colors_idlemsg, $message);
		$message = str_replace('%vote%', $this->config->Colors_vote, $message);
		return $message;
	}

	function mapCountry($country) {
		$countries = array(
						'Afghanistan' => 'AFG',
						'Albania' => 'ALB',
						'Algeria' => 'ALG',
						'Andorra' => 'AND',
						'Angola' => 'ANG',
						'Argentina' => 'ARG',
						'Armenia' => 'ARM',
						'Aruba' => 'ARU',
						'Australia' => 'AUS',
						'Austria' => 'AUT',
						'Azerbaijan' => 'AZE',
						'Bahamas' => 'BAH',
						'Bahrain' => 'BRN',
						'Bangladesh' => 'BAN',
						'Barbados' => 'BAR',
						'Belarus' => 'BLR',
						'Belgium' => 'BEL',
						'Belize' => 'BIZ',
						'Benin' => 'BEN',
						'Bermuda' => 'BER',
						'Bhutan' => 'BHU',
						'Bolivia' => 'BOL',
						'Bosnia&Herzegovina' => 'BIH',
						'Botswana' => 'BOT',
						'Brazil' => 'BRA',
						'Brunei' => 'BRU',
						'Bulgaria' => 'BUL',
						'Burkina Faso' => 'BUR',
						'Burundi' => 'BDI',
						'Cambodia' => 'CAM',
						'Cameroon' => 'CAR',
						'Canada' => 'CAN',
						'Cape Verde' => 'CPV',
						'Central African Republic' => 'CAF',
						'Chad' => 'CHA',
						'Chile' => 'CHI',
						'China' => 'CHN',
						'Chinese Taipei' => 'TPE',
						'Colombia' => 'COL',
						'Congo' => 'CGO',
						'Costa Rica' => 'CRC',
						'Croatia' => 'CRO',
						'Cuba' => 'CUB',
						'Cyprus' => 'CYP',
						'Czech Republic' => 'CZE',
						'Czech republic' => 'CZE',
						'DR Congo' => 'COD',
						'Denmark' => 'DEN',
						'Djibouti' => 'DJI',
						'Dominica' => 'DMA',
						'Dominican Republic' => 'DOM',
						'Ecuador' => 'ECU',
						'Egypt' => 'EGY',
						'El Salvador' => 'ESA',
						'Eritrea' => 'ERI',
						'Estonia' => 'EST',
						'Ethiopia' => 'ETH',
						'Fiji' => 'FIJ',
						'Finland' => 'FIN',
						'France' => 'FRA',
						'Gabon' => 'GAB',
						'Gambia' => 'GAM',
						'Georgia' => 'GEO',
						'Germany' => 'GER',
						'Ghana' => 'GHA',
						'Greece' => 'GRE',
						'Grenada' => 'GRN',
						'Guam' => 'GUM',
						'Guatemala' => 'GUA',
						'Guinea' => 'GUI',
						'Guinea-Bissau' => 'GBS',
						'Guyana' => 'GUY',
						'Haiti' => 'HAI',
						'Honduras' => 'HON',
						'Hong Kong' => 'HKG',
						'Hungary' => 'HUN',
						'Iceland' => 'ISL',
						'India' => 'IND',
						'Indonesia' => 'INA',
						'Iran' => 'IRI',
						'Iraq' => 'IRQ',
						'Ireland' => 'IRL',
						'Israel' => 'ISR',
						'Italy' => 'ITA',
						'Ivory Coast' => 'CIV',
						'Jamaica' => 'JAM',
						'Japan' => 'JPN',
						'Jordan' => 'JOR',
						'Kazakhstan' => 'KAZ',
						'Kenya' => 'KEN',
						'Kiribati' => 'KIR',
						'Korea' => 'KOR',
						'Kuwait' => 'KUW',
						'Kyrgyzstan' => 'KGZ',
						'Laos' => 'LAO',
						'Latvia' => 'LAT',
						'Lebanon' => 'LIB',
						'Lesotho' => 'LES',
						'Liberia' => 'LBR',
						'Libya' => 'LBA',
						'Liechtenstein' => 'LIE',
						'Lithuania' => 'LTU',
						'Luxembourg' => 'LUX',
						'Macedonia' => 'MKD',
						'Malawi' => 'MAW',
						'Malaysia' => 'MAS',
						'Mali' => 'MLI',
						'Malta' => 'MLT',
						'Mauritania' => 'MTN',
						'Mauritius' => 'MRI',
						'Mexico' => 'MEX',
						'Moldova' => 'MDA',
						'Monaco' => 'MON',
						'Mongolia' => 'MGL',
						'Montenegro' => 'MNE',
						'Morocco' => 'MAR',
						'Mozambique' => 'MOZ',
						'Myanmar' => 'MYA',
						'Namibia' => 'NAM',
						'Nauru' => 'NRU',
						'Nepal' => 'NEP',
						'Netherlands' => 'NED',
						'New Zealand' => 'NZL',
						'Nicaragua' => 'NCA',
						'Niger' => 'NIG',
						'Nigeria' => 'NGR',
						'Norway' => 'NOR',
						'Oman' => 'OMA',
						'Other Countries' => 'OTH',
						'Pakistan' => 'PAK',
						'Palau' => 'PLW',
						'Palestine' => 'PLE',
						'Panama' => 'PAN',
						'Paraguay' => 'PAR',
						'Peru' => 'PER',
						'Philippines' => 'PHI',
						'Poland' => 'POL',
						'Portugal' => 'POR',
						'Puerto Rico' => 'PUR',
						'Qatar' => 'QAT',
						'Romania' => 'ROM',
						'Russia' => 'RUS',
						'Rwanda' => 'RWA',
						'Samoa' => 'SAM',
						'San Marino' => 'SMR',
						'Saudi Arabia' => 'KSA',
						'Senegal' => 'SEN',
						'Serbia' => 'SCG',
						'Sierra Leone' => 'SLE',
						'Singapore' => 'SIN',
						'Slovakia' => 'SVK',
						'Slovenia' => 'SLO',
						'Somalia' => 'SOM',
						'South Africa' => 'RSA',
						'Spain' => 'ESP',
						'Sri Lanka' => 'SRI',
						'Sudan' => 'SUD',
						'Suriname' => 'SUR',
						'Swaziland' => 'SWZ',
						'Sweden' => 'SWE',
						'Switzerland' => 'SUI',
						'Syria' => 'SYR',
						'Taiwan' => 'TWN',
						'Tajikistan' => 'TJK',
						'Tanzania' => 'TAN',
						'Thailand' => 'THA',
						'Togo' => 'TOG',
						'Tonga' => 'TGA',
						'Trinidad and Tobago' => 'TRI',
						'Tunisia' => 'TUN',
						'Turkey' => 'TUR',
						'Turkmenistan' => 'TKM',
						'Tuvalu' => 'TUV',
						'Uganda' => 'UGA',
						'Ukraine' => 'UKR',
						'United Arab Emirates' => 'UAE',
						'United Kingdom' => 'GBR',
						'United States of America' => 'USA',
						'Uruguay' => 'URU',
						'Uzbekistan' => 'UZB',
						'Vanuatu' => 'VAN',
						'Venezuela' => 'VEN',
						'Vietnam' => 'VIE',
						'Yemen' => 'YEM',
						'Zambia' => 'ZAM',
						'Zimbabwe' => 'ZIM',
		);

		if (array_key_exists($country, $countries)) {
			$output = $countries[$country];
		} else {
			$output = 'OTH';
			if ($country != '')
				return false;
		}
		return $output;
	}

	function isPlayerOnline($login) {
		if(array_key_exists($login, $this->storage->players)) {
			return true;
		} else {
			if(array_key_exists($login, $this->storage->spectators)) {
			return true;
			}
		}
		return false;
	}

	public function getGameModeName($num){
			$names = array("Script", "Rounds", "TimeAttack", "Team", "Laps", "Cup", "Stunts");
        return (isset($names[$num]) ? $names[$num] : false);
    }

    public function getGameModeNumber($name){
		$name = \strtolower($name);
		$names = array("script" => 0, "rounds"=>1, "timeattack"=>2, "team"=>3, "laps"=>4, "cup"=>5, "stunts"=>6);
		return (isset($names[$name]) ? $names[$name] : false);
    }

}

?>