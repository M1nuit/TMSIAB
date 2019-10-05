<?php

namespace ManiaLivePlugins\MLEPP\Jukebox;

use ManiaLive\Data\Storage;
use ManiaLive\DedicatedApi\Connection;
use ManiaLive\Gui\Windowing\WindowHandler;
use ManiaLive\Gui\Windowing\Windows\Info;
use ManiaLivePlugins\MLEPP\Database\Structures\multiQuery;
use ManiaLivePlugins\MLEPP\Jukebox\Structures\Sql;
use ManiaLivePlugins\MLEPP\Jukebox\Structures\SearchHandler;
use ManiaLivePlugins\MLEPP\Jukebox\Gui\Windows\trackList;
use ManiaLivePlugins\MLEPP\Jukebox\Structures\listCommand;

class DbSearch extends SearchHandler {

	private static $commands = array();
	private $mlepp;

	public function __construct() {
		$this->mlepp = \ManiaLivePlugins\MLEPP\Core\Mlepp::getInstance();
		self::$commands["nofinish"] = new listCommand(); //
		self::$commands["nofinish"]->name = "No Finish";
		self::$commands["nofinish"]->command = "nofinish";
		self::$commands["nofinish"]->functionName = "searchNoFinish";
		self::$commands["nofinish"]->description = "Tracks that you haven't finished";

		self::$commands["noauthortime"] = new listCommand(); //
		self::$commands["noauthortime"]->name = "No Author Time";
		self::$commands["noauthortime"]->command = "noAuthorTime";
		self::$commands["noauthortime"]->functionName = "searchNoAuthorTime";
		self::$commands["noauthortime"]->description = "Tracks on which your Record is slower than the Author Time";

		self::$commands["nogoldtime"] = new listCommand(); //
		self::$commands["nogoldtime"]->name = "No Gold Time";
		self::$commands["nogoldtime"]->command = "noGoldTime";
		self::$commands["nogoldtime"]->functionName = "searchNoGoldTime";
		self::$commands["nogoldtime"]->description = "Tracks on which your Record is slower than the Gold Time";

		self::$commands["nosilvertime"] = new listCommand(); //
		self::$commands["nosilvertime"]->name = "No Silver Time";
		self::$commands["nosilvertime"]->command = "noSilverTime";
		self::$commands["nosilvertime"]->functionName = "searchNoSilverTime";
		self::$commands["nosilvertime"]->description = "Tracks on which your Record is slower than the Silver Time";

		self::$commands["nobronzetime"] = new listCommand(); //
		self::$commands["nobronzetime"]->name = "No Bronze Time";
		self::$commands["nobronzetime"]->command = "noBronzeTime";
		self::$commands["nobronzetime"]->functionName = "searchNoBronzeTime";
		self::$commands["nobronzetime"]->description = "Tracks on which your Record is slower than the Bronze Time";

		self::$commands["noauthortime"] = new listCommand(); //
		self::$commands["noauthortime"]->name = "No Author Time";
		self::$commands["noauthortime"]->command = "noAuthorTime";
		self::$commands["noauthortime"]->functionName = "searchNoAuthorTime";
		self::$commands["noauthortime"]->description = "Tracks on which your Record is slower than the Author Time";

		self::$commands["nogoldtime"] = new listCommand(); //
		self::$commands["nogoldtime"]->name = "No Gold Time";
		self::$commands["nogoldtime"]->command = "noGoldTime";
		self::$commands["nogoldtime"]->functionName = "searchNoGoldTime";
		self::$commands["nogoldtime"]->description = "Tracks on which your Record is slower than the Gold Time";

		self::$commands["nosilvertime"] = new listCommand(); //
		self::$commands["nosilvertime"]->name = "No Silver Time";
		self::$commands["nosilvertime"]->command = "noSilverTime";
		self::$commands["nosilvertime"]->functionName = "searchNoSilverTime";
		self::$commands["nosilvertime"]->description = "Tracks on which your Record is slower than the Silver Time";

		self::$commands["nobronzetime"] = new listCommand(); //
		self::$commands["nobronzetime"]->name = "No Bronze Time";
		self::$commands["nobronzetime"]->command = "noBronzeTime";
		self::$commands["nobronzetime"]->functionName = "searchNoBronzeTime";
		self::$commands["nobronzetime"]->description = "Tracks on which your Record is slower than the Bronze Time";

		self::$commands["authortime"] = new listCommand(); //
		self::$commands["authortime"]->name = "Author Time";
		self::$commands["authortime"]->command = "authorTime";
		self::$commands["authortime"]->functionName = "searchAuthorTime";
		self::$commands["authortime"]->description = "Tracks on which your Record is faster than the Author Time";

		self::$commands["goldtime"] = new listCommand(); //
		self::$commands["goldtime"]->name = "Gold Time";
		self::$commands["goldtime"]->command = "goldTime";
		self::$commands["goldtime"]->functionName = "searchGoldTime";
		self::$commands["goldtime"]->description = "Tracks on which your Record is faster than the Gold Time";

		self::$commands["silvertime"] = new listCommand(); //
		self::$commands["silvertime"]->name = "Silver Time";
		self::$commands["silvertime"]->command = "silverTime";
		self::$commands["silvertime"]->functionName = "searchSiverTime";
		self::$commands["silvertime"]->description = "Tracks on which your Record is faster than the Silver Time";

		self::$commands["bronzetime"] = new listCommand(); //
		self::$commands["bronzetime"]->name = "Bronze Time";
		self::$commands["bronzetime"]->command = "bronzeTime";
		self::$commands["bronzetime"]->functionName = "searchBronzeTime";
		self::$commands["bronzetime"]->description = "Tracks on which your Record is faster than the Bronze Time";

		self::$commands["rank"] = new listCommand();
		self::$commands["rank"]->name = "Rank";
		self::$commands["rank"]->command = "rank";
		self::$commands["rank"]->functionName = "searchRank";
		self::$commands["rank"]->description = "Tracks on which you have the n'th record";

		self::$commands["first"] = new listCommand();
		self::$commands["first"]->name = "In first";
		self::$commands["first"]->command = "first";
		self::$commands["first"]->functionName = "searchRankFirst";
		self::$commands["first"]->description = "Tracks on which you record is in the first n";

		self::$commands["nofirst"] = new listCommand();
		self::$commands["nofirst"]->name = "Not In first";
		self::$commands["nofirst"]->command = "noFirst";
		self::$commands["nofirst"]->functionName = "searchRankNoFirst";
		self::$commands["nofirst"]->description = "Tracks on which you record isn't in the first n";

		self::$commands["worst"] = new listCommand();
		self::$commands["worst"]->name = "Worst Rankings";
		self::$commands["worst"]->command = "worst";
		self::$commands["worst"]->functionName = "searchWorstRanks";
		self::$commands["worst"]->description = "Orders the list from your worst rankings to the best";

		self::$commands["best"] = new listCommand();
		self::$commands["best"]->name = "Best Rankings";
		self::$commands["best"]->command = "Best";
		self::$commands["best"]->functionName = "searchBestRanks";
		self::$commands["best"]->description = "Orders the list from your best rankings to the worst";

		self::$commands["worsethan"] = new listCommand(); //
		self::$commands["worsethan"]->name = "Worse Than";
		self::$commands["worsethan"]->command = "worsethan";
		self::$commands["worsethan"]->functionName = "searchWorseThan";
		self::$commands["worsethan"]->description = "Tracks your record is worse than that of someone else";

		self::$commands["env"] = new listCommand();
		self::$commands["env"]->name = "Environment";
		self::$commands["env"]->command = "env";
		self::$commands["env"]->functionName = "searchEnvironment";
		self::$commands["env"]->description = "Tracks in the asked environment.\nParams you can use : bay/coast/speed/island/rally/snow/stadium";

		self::$commands["longer"] = new listCommand();
		self::$commands["longer"]->name = "Longer Then";
		self::$commands["longer"]->command = "longer";
		self::$commands["longer"]->functionName = "searchLonger";
		self::$commands["longer"]->description = "Tracks longer then the value passed in params. \n\$sExample : \$z/list longer 30 Will show tracks longer than 30sec";

		self::$commands["shorter"] = new listCommand();
		self::$commands["shorter"]->name = "Shorter Then";
		self::$commands["shorter"]->command = "shorter";
		self::$commands["shorter"]->functionName = "searchShorter";
		self::$commands["shorter"]->description = "Tracks shorter then the value passed in params. \n\$sExample : \$z/list shorter 90 Will show tracks shorter than 1min and 30seconds";

		self::$commands["tmx_type"] = new listCommand();
		self::$commands["tmx_type"]->name = "Tmx Type";
		self::$commands["tmx_type"]->command = "tmx_type";
		self::$commands["tmx_type"]->functionName = "searchTmx_type";
		self::$commands["tmx_type"]->description = "Allows you to find tracks according to it's tmx type. \n You can use : Race, Puzzle, Platform, Stunts, Shortcut. \n Only works with tracks which have TMX information";

		self::$commands["tmx_style"] = new listCommand();
		self::$commands["tmx_style"]->name = "Tmx Style";
		self::$commands["tmx_style"]->command = "tmx_style";
		self::$commands["tmx_style"]->functionName = "searchTmx_style";
		self::$commands["tmx_style"]->description = "Allows you to find tracks according to it's tmx style. \n You can use : Normal, Stunt, Maze, Offroad, Laps, FullSpeed, Lol, Tech, SpeedTech, RPG, Press Forward. \n Only works with tracks which have TMX information";


		self::$commands["tmx_difficulty"] = new listCommand();
		self::$commands["tmx_difficulty"]->name = "Tmx Difficulty";
		self::$commands["tmx_difficulty"]->command = "tmx_difficulty";
		self::$commands["tmx_difficulty"]->functionName = "searchTmx_difficulty";
		self::$commands["tmx_difficulty"]->description = "Allows you to find tracks according to it's tmx difficulty. \n You can use : Beginner, Intermediate, Expert, Lunatic. \n Only works with tracks which have TMX information";

		self::$commands["tmx_dif"] = new listCommand();
		self::$commands["tmx_dif"]->name = "Tmx Difficulty";
		self::$commands["tmx_dif"]->command = "tmx_dif";
		self::$commands["tmx_dif"]->functionName = "searchTmx_difficulty";
		self::$commands["tmx_dif"]->description = "Shortcut of /list tmx_difficulty command";

		self::$commands["cmd"] = new listCommand();
		self::$commands["cmd"]->name = "List Help";
		self::$commands["cmd"]->command = "cmd";
		self::$commands["cmd"]->functionName = "showhelp";
		self::$commands["cmd"]->description = "Show this window";
		self::$commands["cmd"]->needWindow = false;
	}

	public function tracklist($login, $param1, $param2) {

		$stor = Storage::getInstance();

		$loginObj = Storage::GetInstance()->getPlayerObject($login);

		if ($param1 == null) {
			$sql = new Sql();
			$sql->setFrom("challenges c, serverchallengelist l");
			$sql->setWhere("c.challenge_id = l.challenge_id AND server_login='" . $stor->serverLogin . "'");
			$title = "All Tracks";
		} else {

			$param1 = strtolower($param1);

			if (isset(self::$commands[$param1])) {

				$function = self::$commands[$param1]->functionName;
				$title = self::$commands[$param1]->name;
				if (self::$commands[$param1]->needWindow) {
					$sql = $this->$function($login, $param1, $param2);

					if (!$sql) {
						return;
					}
				} else {
					$this->$function($login, $param1, $param2);
					return;
				}
			} else {

				$sql = $this->searchAuthorTime($login);

				$sql = $this->search($login, $param1, $param2);

				$param1 = "search";
				$title = "Custom Search";
			}
		}


		$sql->setSelect("Count(*) as Nb");
		$sql->setLimit(null);

		$nb = $this->mlepp->db->query($sql);
		$nb = $nb->fetchAssoc($nb);

		if ($nb["Nb"] == 0) {
			$info = Info::Create($login);
			$info->setSize(80, 40);
			$info->setTitle('no such tracks');
			$info->setText("/list wasn't able to find any tracks according to your criteria");
			$info->centerOnScreen();
			WindowHandler::showDialog($info);
			return false;
		}
		if (!isset($this->allocatedWindows[$login])) {
			$this->allocatedWindows[$login] = trackList::Create($login);
		}

		$this->allocatedWindows[$login]->setSize(210, 100);
		$this->allocatedWindows[$login]->setTitle("Server TrackList : " . $title);
		$this->allocatedWindows[$login]->clearRecords();

		$this->setWindowColumns($this->allocatedWindows[$login], "list_" . $param1);
		$this->allocatedWindows[$login]->setSQL($sql, $nb["Nb"]);

		//To set uplater

		$this->allocatedWindows[$login]->centerOnScreen();
		$this->allocatedWindows[$login]->show();
	}

	public function setColumns($name, $columns) {
		$this->columns[$name] = $columns;
	}

	public function getColumns($name) {
		if (isset($this->columns[$name]))
			return $this->columns[$name];

		return false;
	}

	private function setWindowColumns($window, $name) {
		if (isset($this->columns[$name]))
			$window->setColumns($this->columns[$name]);
		else
			$window->setColumns($this->columns["list_default"]);
	}

	public function getChallangeFromNum($id, $login) {

		$stor = Storage::getInstance();



		$sql = "SELECT *
                        FROM challenges c, serverchallengelist l
                        WHERE c.challenge_id = $id
                            AND c.challenge_id = l.challenge_id
                            AND server_login='" . $stor->serverLogin . "'";

		$response = $this->mlepp->db->query($sql);

		if ($response->recordCount() > 0)
			return $response->fetchAssoc();
		else
			return false;
	}

	/*	 * *****************
	 * Search Functions
	 */

	private function search($login, $param1) {
		$stor = Storage::getInstance();

		$sql = new Sql();

		$sql->setFrom("challenges c, serverchallengelist l");
		$sql->setWhere("c.challenge_id = l.challenge_id
                            AND l.server_login='" . $stor->serverLogin . "'
                            AND (c.challenge_nameStripped LIKE '%" . $param1 . "%'
                                    OR challenge_author LIKE '%" . $param1 . "%') ");
		return $sql;
	}

	private function searchNoFinish($login) {

		$stor = Storage::getInstance();

		$sql = new Sql();

		$sql->setFrom("challenges c, serverchallengelist l");
		$sql->setWhere("c.challenge_id = l.challenge_id
                            AND l.server_login='" . $stor->serverLogin . "'
                            AND c.challenge_uid NOT IN (SELECT record_challengeuid FROM localrecords
                                                            WHERE record_playerlogin = '" . $login . "')");

		return $sql;
	}

	private function searchNoAuthorTime($login) {

		$stor = Storage::getInstance();

		$sql = new Sql();

		$sql->setFrom("challenges c, serverchallengelist l, localrecords lr");
		$sql->setWhere("c.challenge_id = l.challenge_id
                            AND lr.record_challengeuid = c.challenge_uid
                            AND lr.record_playerlogin = '" . $login . "'
                            AND lr.record_score > c.challenge_authorTime
                            AND l.server_login='" . $stor->serverLogin . "'");

		return $sql;
	}

	private function searchNoGoldTime($login) {

		$stor = Storage::getInstance();

		$sql = new Sql();

		$sql->setFrom("challenges c, serverchallengelist l, localrecords lr");
		$sql->setWhere("c.challenge_id = l.challenge_id
                            AND lr.record_challengeuid = c.challenge_uid
                            AND lr.record_playerlogin = '" . $login . "'
                            AND lr.record_score > c.challenge_goldTime
                            AND l.server_login='" . $stor->serverLogin . "'");

		return $sql;
	}

	private function searchNoSilverTime($login) {

		$stor = Storage::getInstance();

		$sql = new Sql();

		$sql->setFrom("challenges c, serverchallengelist l, localrecords lr");
		$sql->setWhere("c.challenge_id = l.challenge_id
                            AND lr.record_challengeuid = c.challenge_uid
                            AND lr.record_playerlogin = '" . $login . "'
                            AND lr.record_score > c.challenge_silverTime
                            AND l.server_login='" . $stor->serverLogin . "'");

		return $sql;
	}

	private function searchNoBronzeTime($login) {

		$stor = Storage::getInstance();

		$sql = new Sql();

		$sql->setFrom("challenges c, serverchallengelist l, localrecords lr");
		$sql->setWhere("c.challenge_id = l.challenge_id
                            AND lr.record_challengeuid = c.challenge_uid
                            AND lr.record_playerlogin = '" . $login . "'
                            AND lr.record_score > c.challenge_bronzeTime
                            AND l.server_login='" . $stor->serverLogin . "'");

		return $sql;
	}

	private function searchFinished($login) {

		$stor = Storage::getInstance();

		$sql = new Sql();

		$sql->setFrom("challenges c, serverchallengelist l");
		$sql->setWhere("c.challenge_id = l.challenge_id
                            AND l.server_login='" . $stor->serverLogin . "'
                            AND c.challenge_uid IN (SELECT record_challengeuid FROM localrecords
                                                            WHERE record_playerlogin = '" . $login . "')");

		return $sql;
	}

	private function searchAuthorTime($login) {

		$stor = Storage::getInstance();

		$sql = new Sql();

		$sql->setFrom("challenges c, serverchallengelist l, localrecords lr");
		$sql->setWhere("c.challenge_id = l.challenge_id
                            AND lr.record_challengeuid = c.challenge_uid
                            AND lr.record_playerlogin = '" . $login . "'
                            AND lr.record_score < c.challenge_authorTime
                            AND l.server_login='" . $stor->serverLogin . "'");

		return $sql;
	}

	private function searchGoldTime($login) {

		$stor = Storage::getInstance();

		$sql = new Sql();

		$sql->setFrom("challenges c, serverchallengelist l, localrecords lr");
		$sql->setWhere("c.challenge_id = l.challenge_id
                            AND lr.record_challengeuid = c.challenge_uid
                            AND lr.record_playerlogin = '" . $login . "'
                            AND lr.record_score < c.challenge_goldTime
                            AND l.server_login='" . $stor->serverLogin . "'");

		return $sql;
	}

	private function searchSilverTime($login) {

		$stor = Storage::getInstance();

		$sql = new Sql();

		$sql->setFrom("challenges c, serverchallengelist l, localrecords lr");
		$sql->setWhere("c.challenge_id = l.challenge_id
                            AND lr.record_challengeuid = c.challenge_uid
                            AND lr.record_playerlogin = '" . $login . "'
                            AND lr.record_score < c.challenge_silverTime
                            AND l.server_login='" . $stor->serverLogin . "'");

		return $sql;
	}

	private function searchBronzeTime($login) {

		$stor = Storage::getInstance();

		$sql = new Sql();

		$sql->setFrom("challenges c, serverchallengelist l, localrecords lr");
		$sql->setWhere("c.challenge_id = l.challenge_id
                            AND lr.record_challengeuid = c.challenge_uid
                            AND lr.record_playerlogin = '" . $login . "'
                            AND lr.record_score < c.challenge_bronzeTime
                            AND l.server_login='" . $stor->serverLogin . "'");

		return $sql;
	}

	private function searchRank($login, $param1, $param2) {

		if (empty($param2) || $param2 <= 0) {
			$info = Info::Create($login);
			$info->setSize(50, 20);
			$info->setTitle('Wrong use of /list Rank');
			$info->setText("You need to specify a Rank as third parameter\n\$f00Example : \$z \list Rank 10 \n Will show you tracks where you've got the tenth record");
			$info->centerOnScreen();
			WindowHandler::showDialog($info);
			return false;
		}

		$stor = Storage::getInstance();

		$sql = new Sql();

		$sql->setFrom("challenges c, serverchallengelist l, localrecords lr");
		$sql->setWhere($this->getWhere_searchByRank($login, $param2));

		return $sql;
	}

	private function searchRankFirst($login, $param1, $param2) {

		if (empty($param2) || $param2 <= 0) {
			$info = Info::Create($login);
			$info->setSize(50, 20);
			$info->setTitle('Wrong use of /list First');
			$info->setText("You need to specify a Rank as third parameter\n\$f00Example : \$z \list First 10 \n Will show you the tracks in which your record is within the first ten");
			$info->centerOnScreen();
			WindowHandler::showDialog($info);
			return false;
		}

		$stor = Storage::getInstance();

		$sql = new Sql();

		$sql->setFrom("challenges c, serverchallengelist l, localrecords lr");
		$sql->setWhere($this->getWhere_searchByRank($login, 3, 1));

		return $sql;
	}

	private function searchRankNoFirst($login, $param1, $param2) {

		if (empty($param2) || $param2 <= 0) {
			$info = Info::Create($login);
			$info->setSize(50, 20);
			$info->setTitle('Wrong use of /list NoFirst');
			$info->setText("You need to specify a Rank as third parameter\n\$f00Example : \$z \list noFirst 10 \n Will show you the tracks in which your record isn't within the first ten");
			$info->centerOnScreen();
			WindowHandler::showDialog($info);
			return false;
		}

		$stor = Storage::getInstance();

		$sql = new Sql();

		$sql->setFrom("challenges c, serverchallengelist l, localrecords lr");
		$sql->setWhere($this->getWhere_searchByRank($login, $param2, 2));

		return $sql;
	}

	private function getWhere_searchByRank($login, $rank, $exact = 0) {
		$stor = Storage::getInstance();

		if ($exact == 0) {
			$cmp = ($rank - 1) . " =";
		} elseif ($exact == 1) {
			$cmp = ($rank - 1) . " >=";
		} else {
			$cmp = ($rank - 1) . " <=";
		}

		$where = "l.server_login='" . $stor->serverLogin . "'
                AND c.challenge_id = l.challenge_id
                AND lr.record_challengeuid = c.challenge_uid
                AND lr.record_playerlogin = '" . $login . "'
                AND " . $cmp . " (SELECT COUNT(*) FROM localrecords lr2
                            WHERE lr2.record_challengeuid = lr.record_challengeuid
                                AND lr2.record_score < lr.record_score)";

		return $where;
	}

	private function searchWorstRanks($login, $param1, $param2) {
		$stor = Storage::getInstance();

		$sql = new Sql();

		$sql->setSelect2("(SELECT COUNT(*) FROM localrecords lr2
								WHERE lr2.record_challengeuid = lr.record_challengeuid
                                AND lr2.record_score < lr.record_score
								GROUP BY lr2.record_challengeuid) AS playerRank");
		$sql->setFrom("challenges c, serverchallengelist l, localrecords lr");

		$where = "l.server_login='" . $stor->serverLogin . "'
                AND c.challenge_id = l.challenge_id
                AND lr.record_challengeuid = c.challenge_uid
                AND lr.record_playerlogin = '" . $login . "'";


		$sql->setWhere($where);
		$sql->setSortBy("playerRank");

		return $sql;
	}

	private function searchBestRanks($login, $param1, $param2) {
		$stor = Storage::getInstance();

		$sql = new Sql();

		$sql->setSelect2("(SELECT COUNT(*) FROM localrecords lr2
								WHERE lr2.record_challengeuid = lr.record_challengeuid
                                AND lr2.record_score < lr.record_score
								GROUP BY lr2.record_challengeuid) AS playerRank");
		$sql->setFrom("challenges c, serverchallengelist l, localrecords lr");

		$where = "l.server_login='" . $stor->serverLogin . "'
                AND c.challenge_id = l.challenge_id
                AND lr.record_challengeuid = c.challenge_uid
                AND lr.record_playerlogin = '" . $login . "'";


		$sql->setWhere($where);
		$sql->setSortBy("playerRank", "ASC");

		return $sql;
	}

	private function searchWorseThan($login, $param1, $param2) {

		if (empty($param2) || $param2 < 0) {
			$info = Info::Create($login);
			$info->setSize(50, 20);
			$info->setTitle('Wrong use of /list worsethan');
			$text = "You need to specify the login you want to compare against\n";
			$text.="\$f00Example : \$z /list worsethan theotherguy\n";
			$text.="Will show you tracks where your record is worse than that of theotherguy";
			$info->setText($text);
			$info->centerOnScreen();
			WindowHandler::showDialog($info);
			return false;
		}

		$stor = Storage::getInstance();

		$sql = new Sql();

		$sql->setFrom("challenges c, serverchallengelist l, localrecords lr_me, localrecords lr_him");
		$sql->setWhere("c.challenge_id = l.challenge_id
                            AND lr_me.record_challengeuid = c.challenge_uid
                            AND lr_me.record_playerlogin = '" . $login . "'
                            AND lr_him.record_challengeuid = c.challenge_uid
                            AND lr_him.record_playerlogin = '" . $param2 . "'
                            AND lr_me.record_score > lr_him.record_score
                            AND l.server_login='" . $stor->serverLogin . "'");

		return $sql;
	}

	private function searchEnvironment($login, $param1, $param2) {
		$enviMap = array("stadium" => "Stadium",
			"snow" => "Snow",
			"rally" => "Rally",
			"island" => "Island",
			"speed" => "Speed",
			"coast" => "Coast",
			"bay" => "Bay");

		$param2 = \strtolower($param2);

		if (empty($param2) || !isset($enviMap[$param2])) {
			$info = Info::Create($login);
			$info->setSize(50, 20);
			$info->setTitle('Wrong use of /list env');
			$text = "You need to specify an Environment name as third parameter.\n";
			$text.="\$f00Example : \$z \list env stadium";
			$text.="\n Will show stadium tracks. You can use any of these parameters : bay/coast/speed/island/rally/snow/stadium";
			$info->setText($text);
			$info->centerOnScreen();
			WindowHandler::showDialog($info);
			return false;
		}

		$stor = Storage::getInstance();

		$sql = new Sql();

		$sql->setFrom("challenges c, serverchallengelist l");
		$sql->setWhere("c.challenge_id = l.challenge_id
                            AND c.challenge_environment = '" . $enviMap[$param2] . "'
                            AND l.server_login='" . $stor->serverLogin . "'");

		return $sql;
	}

	private function searchLonger($login, $param1, $param2) {

		if (empty($param2) || $param2 < 0) {
			$info = Info::Create($login);
			$info->setSize(50, 20);
			$info->setTitle('Wrong use of /list longer');
			$text = "You need to specify the minimum lenght in seconds\n";
			$text.="\$f00Example : \$z \list longer 30";
			$text.="\n Will show you tracks longer than 30 seconds";
			$info->setText($text);
			$info->centerOnScreen();
			WindowHandler::showDialog($info);
			return false;
		}
		$param2 = (int) $param2;
		$param2 = $param2 * 1000;

		$stor = Storage::getInstance();

		$sql = new Sql();

		$sql->setFrom("challenges c, serverchallengelist l");
		$sql->setWhere("c.challenge_id = l.challenge_id
                            AND c.challenge_authorTime >= " . $param2 . "
                            AND l.server_login='" . $stor->serverLogin . "'");
		return $sql;
	}

	private function searchShorter($login, $param1, $param2) {

		if (empty($param2) || $param2 < 0) {
			$info = Info::Create($login);
			$info->setSize(50, 20);
			$info->setTitle('Wrong use of /list shorter');
			$text = "You need to specify the maximum lenght in seconds\n";
			$text.="\$f00Example : \$z \list shorter 30";
			$text.="\n Will show you tracks shorter than 30 seconds";
			$info->setText($text);
			$info->centerOnScreen();
			WindowHandler::showDialog($info);
			return false;
		}
		$param2 = (int) $param2;
		$param2 = $param2 * 1000;

		$stor = Storage::getInstance();

		$sql = new Sql();

		$sql->setFrom("challenges c, serverchallengelist l");
		$sql->setWhere("c.challenge_id = l.challenge_id
                            AND c.challenge_authorTime <= " . $param2 . "
                            AND l.server_login='" . $stor->serverLogin . "'");
		return $sql;
	}

	private function searchTmx_type($login, $param1, $param2) {

		$types = array("race", "puzzle", "platform", "stunts", "shortcut");

		$param2 = \strtolower($param2);

		if (empty($param2) || !\in_array($param2, $types)) {
			$info = Info::Create($login);
			$info->setSize(50, 20);
			$info->setTitle('Wrong use of /list tmx_Type');
			$text = "You need to specify tmx type you search\n";
			$text.="\$f00Example : \$z \list tmx_type Race";
			$text.="\n You can use : Race, Puzzle, Platform, Stunts, Shortcut";
			$info->setText($text);
			$info->centerOnScreen();
			WindowHandler::showDialog($info);
			return false;
		}

		$stor = Storage::getInstance();

		$sql = new Sql();

		$sql->setFrom("challenges c, serverchallengelist l, tmxdata t");
		$sql->setWhere("c.challenge_id = l.challenge_id
                            AND c.challenge_uid = t.tmx_trackuid
                            AND t.tmx_type = " . $this->mlepp->db->quote($param2) . "
                            AND l.server_login='" . $stor->serverLogin . "'");
		return $sql;
	}

	private function searchTmx_style($login, $param1, $param2) {

		$styles = array("normal", "stunt", "maze", "offroad", "laps", "fullspeed", "lol", "tech", "speedtech", "rpg", "Pressforward");

		$param2 = \strtolower($param2);

		if (empty($param2) || !\in_array($param2, $styles)) {
			$info = Info::Create($login);
			$info->setSize(50, 20);
			$info->setTitle('Wrong use of /list tmx_Type');
			$text = "You need to specify tmx styles you search\n";
			$text.="\$f00Example : \$z \list tmx_style Offroad";
			$text.="\n You can use : Normal, Stunt, Maze, Offroad, Laps, FullSpeed, Lol, Tech, SpeedTech, RPG, PressForward";
			$info->setText($text);
			$info->centerOnScreen();
			WindowHandler::showDialog($info);
			return false;
		}

		$stor = Storage::getInstance();

		$sql = new Sql();

		$sql->setFrom("challenges c, serverchallengelist l, tmxdata t");
		$sql->setWhere("c.challenge_id = l.challenge_id
                            AND c.challenge_uid = t.tmx_trackuid
                            AND t.tmx_style = " . $this->mlepp->db->quote($param2) . "
                            AND l.server_login='" . $stor->serverLogin . "'");
		return $sql;
	}

	private function searchTmx_difficulty($login, $param1, $param2) {

		$styles = array("beginner", "intermediate", "expert", "lunatic");

		$param2 = \strtolower($param2);

		if (empty($param2) || !\in_array($param2, $styles)) {
			$info = Info::Create($login);
			$info->setSize(50, 20);
			$info->setTitle('Wrong use of /list tmx_difficulty');
			$text = "You need to specify tmx styles you search\n";
			$text.="\$b  Example : \$z \list tmx_difficulty Expert";
			$text.="\n You can use : Beginner, Intermediate, Expert, Lunatic";
			$info->setText($text);
			$info->centerOnScreen();
			WindowHandler::showDialog($info);
			return false;
		}

		$stor = Storage::getInstance();

		$sql = new Sql();

		$sql->setFrom("challenges c, serverchallengelist l, tmxdata t");
		$sql->setWhere("c.challenge_id = l.challenge_id
                            AND c.challenge_uid = t.tmx_trackuid
                            AND t.tmx_difficulty = " . $this->mlepp->db->quote($param2) . "
                            AND l.server_login='" . $stor->serverLogin . "'");
		return $sql;
	}

	private function showhelp($login, $param1, $param2) {

		if (!isset($this->allocatedWindows[$login])) {
			$this->allocatedWindows[$login] = trackList::Create($login);
		}
		$this->allocatedWindows[$login]->setSize(60, 61);

		$this->allocatedWindows[$login]->clearRecords();

		$this->setWindowColumns($this->allocatedWindows[$login], "list_" . $param1);

		foreach (self::$commands as $command) {
			$cmd = array();

			$descriptions = explode("\n", $command->description);

			$cmd["Help_Command"] = $command->command;
			$cmd["Help_Description"] = $descriptions[0];
			$this->allocatedWindows[$login]->addRecord($cmd);

			$i = 1;
			while (isset($descriptions[$i])) {
				$cmd["Help_Command"] = "";
				$cmd["Help_Description"] = $descriptions[$i];
				$this->allocatedWindows[$login]->addRecord($cmd);
				$i++;
			}
		}

		$this->allocatedWindows[$login]->setIdentifier("Help_Command");
		$this->allocatedWindows[$login]->setTitle("Server Track list : Help");
		$this->allocatedWindows[$login]->centerOnScreen();
		$this->allocatedWindows[$login]->show();
	}

	public function playerDisconnected($login) {
		if (isset($this->allocatedWindows[$login])) {
			$this->allocatedWindows[$login]->Erase($login);
			unset($this->allocatedWindows[$login]);
		}
//		unset($this->mlepp->db);
	}

}

?>
