<?php

namespace ManiaLivePlugins\MLEPP\Jukebox\Adapter;

use ManiaLive\Utilities\Time;

class oliverde8HudMenu {

	private $jbPlugin;
	private $menuPlugin;

	public function __construct($jbPlugin, $menu) {

		$this->jbPlugin = $jbPlugin;
		$this->menuPlugin = $menu;

		$this->generate_PlayerButtons();
		$this->generatePlayerHelp();
	}

	private function generate_PlayerButtons() {
		$menu = $this->menuPlugin;

		$parent = $menu->findButton(array("Menu", "Tracks and JB"));
		$button["plugin"] = $this->jbPlugin;

		if (!$parent) {
			//caption="Tracks and JB" style="Icons128x128_1" substyle="ProfileVehicle"
			$button["style"] = "Icons128x128_1";
			$button["substyle"] = "ProfileVehicle";

			$parent = $menu->addButton("Menu", "Tracks and JB", $button);
		}

		$this->generatePlayerSearch($parent);

		$button["style"] = "Icons128x128_1";
		$button["substyle"] = "Browse";
		$button["function"] = "trackList";
		$button["params"] = "";
		$menu->addButton($parent, "All Tracks", $button);

		$button["style"] = "Icons128x128_1";
		$button["substyle"] = "ProfileVehicle";
		$button["function"] = "juke";
		$button["params"] = "list";
		$menu->addButton($parent, "JukeBox Content", $button);
	}

	private function generatePlayerSearch($parent) {
		$menu = $this->menuPlugin;

		$button["plugin"] = $this->jbPlugin;

		$button["style"] = "Icons64x64_1";
		$button["substyle"] = "Maximize";
		$search = $menu->addButton($parent, "Search Tracks", $button);

		$button["style"] = "Icons64x64_1";
		$button["substyle"] = "TrackInfo";
		$button["function"] = "trackList";
		$button["params"] = "help";
		$menu->addButton($search, "TrackList Help", $button);

		$this->generatePlayerSearch_byTime($search);
		$this->generatePlayerSearch_byRank($search);
		$this->generatePlayerSearch_byEnvironment($search);
		$this->generatePlayerSearch_byTmx($search);
	}

	private function generatePlayerSearch_byTime($parent) {
		$menu = $this->menuPlugin;

		$button["style"] = "BgRaceScore2";
		$button["substyle"] = "ScoreLink";
		$parent = $menu->addButton($parent, "Search By Time", $button);

		$separator["seperator"] = true;

		$button["plugin"] = $this->jbPlugin;

		$button["style"] = "MedalsBig";
		$button["substyle"] = "MedalNadeo";
		$button["function"] = "trackList";
		$button["params"] = "noAuthorTime";
		$menu->addButton($parent, "No Author Time", $button);

		$button["style"] = "MedalsBig";
		$button["substyle"] = "MedalGold";
		$button["function"] = "trackList";
		$button["params"] = "noGoldTime";
		$menu->addButton($parent, "No Gold Time", $button);

		$button["style"] = "MedalsBig";
		$button["substyle"] = "MedalSilver";
		$button["function"] = "trackList";
		$button["params"] = "noSilverTime";
		$menu->addButton($parent, "No Silver Time", $button);

		$button["style"] = "MedalsBig";
		$button["substyle"] = "MedalBronze";
		$button["function"] = "trackList";
		$button["params"] = "noBronzeTime";
		$menu->addButton($parent, "No Bronze Time", $button);

		$menu->addButton($parent, "You don't have", $separator);

		$button["style"] = "MedalsBig";
		$button["substyle"] = "MedalNadeo";
		$button["function"] = "trackList";
		$button["params"] = "AuthorTime";
		$menu->addButton($parent, "Author Time", $button);

		$button["style"] = "MedalsBig";
		$button["substyle"] = "MedalGold";
		$button["function"] = "trackList";
		$button["params"] = "noGoldTime";
		$menu->addButton($parent, "Gold Time", $button);

		$button["style"] = "MedalsBig";
		$button["substyle"] = "MedalSilver";
		$button["function"] = "trackList";
		$button["params"] = "noSilverTime";
		$menu->addButton($parent, "Silver Time", $button);

		$button["style"] = "MedalsBig";
		$button["substyle"] = "MedalBronze";
		$button["function"] = "trackList";
		$button["params"] = "noBronzeTime";
		$menu->addButton($parent, "Bronze Time", $button);

		$menu->addButton($parent, "You have", $separator);
	}

	private function generatePlayerSearch_byRank($parent) {

		$menu = $this->menuPlugin;

		$button["plugin"] = $this->jbPlugin;

		$button["style"] = "Icons128x128_1";
		$button["substyle"] = "Rankings";
		$parent = $menu->addButton($parent, "Search By Rank", $button);

		$button["style"] = "MedalsBig";
		$button["substyle"] = "MedalSlot";
		$button["function"] = "trackList";
		$button["params"] = "noFinish";
		$menu->addButton($parent, "No Finish", $button);

		$button["style"] = "Icons64x64_1";
		$button["substyle"] = "RedLow";
		$button["function"] = "trackList";
		$button["params"] = "worst";
		$menu->addButton($parent, "Worst Ranks", $button);
		
		$button["style"] = "Icons64x64_1";
		$button["substyle"] = "RedHigh";
		$button["function"] = "trackList";
		$button["params"] = "best";
		$menu->addButton($parent, "Best Ranks", $button);

		$this->generatePlayerSearch_byRankExact($parent);
		$this->generatePlayerSearch_byRankBetter($parent);
		$this->generatePlayerSearch_byRankWorse($parent);
	}

	private function generatePlayerSearch_byRankExact($parent) {
		$menu = $this->menuPlugin;

		$button["style"] = "BgRaceScore2";
		$button["substyle"] = "Podium";
		$parent = $menu->addButton($parent, "Rank is", $button);

		$separator["seperator"] = true;

		$button["plugin"] = $this->jbPlugin;
		$button["style"] = "Icons64x64_1";
		$button["substyle"] = "First";
		$button["function"] = "trackList";
		$button["params"] = "Rank;1";
		$menu->addButton($parent, "First Record", $button);

		$button["style"] = "Icons64x64_1";
		$button["substyle"] = "Second";
		$button["function"] = "trackList";
		$button["params"] = "Rank;2";
		$menu->addButton($parent, "Second Record", $button);

		$button["style"] = "Icons64x64_1";
		$button["substyle"] = "Third";
		$button["function"] = "trackList";
		$button["params"] = "Rank;3";
		$menu->addButton($parent, "Third Record", $button);

		$menu->addButton($parent, "/list rank #", $separator);
	}

	private function generatePlayerSearch_byRankBetter($parent) {
		$menu = $this->menuPlugin;

		$button["plugin"] = $this->jbPlugin;

		$separator["seperator"] = true;

		$button["style"] = "Icons64x64_1";
		$button["substyle"] = "StateSuggested";
		$parent = $menu->addButton($parent, "Better Rank then", $button);

		unset($button["style"]);
		unset($button["substyle"]);
		$button["function"] = "trackList";
		$button["params"] = "first;3";
		$menu->addButton($parent, "Three", $button);

		$button["function"] = "trackList";
		$button["params"] = "first;10";
		$menu->addButton($parent, "Ten", $button);

		$button["function"] = "trackList";
		$button["params"] = "first;20";
		$menu->addButton($parent, "Twenty", $button);

		$menu->addButton($parent, "/list first #", $separator);
	}

	private function generatePlayerSearch_byRankWorse($parent) {
		$menu = $this->menuPlugin;

		$button["plugin"] = $this->jbPlugin;

		$separator["seperator"] = true;

		$button["style"] = "Icons128x128_1";
		$button["substyle"] = "Hard";
		$parent = $menu->addButton($parent, "Worse Rank then", $button);

		unset($button["style"]);
		unset($button["substyle"]);
		$button["function"] = "trackList";
		$button["params"] = "nofirst;3";
		$menu->addButton($parent, "Three", $button);

		$button["function"] = "trackList";
		$button["params"] = "nofirst;10";
		$menu->addButton($parent, "Ten", $button);

		$button["function"] = "trackList";
		$button["params"] = "nofirst;20";
		$menu->addButton($parent, "Twenty", $button);

		$menu->addButton($parent, "/list nofirst #", $separator);
	}

	private function generatePlayerSearch_byEnvironment($parent) {
		$envis = array('Stadium', 'Bay', 'Coast', 'Speed', 'Island', 'Rally', 'Alpine');

		$menu = $this->menuPlugin;

		$button["plugin"] = $this->jbPlugin;

		$button["style"] = "Icons64x64_1";
		$button["substyle"] = "ToolRoot";
		$parent = $menu->addButton($parent, "Environment", $button);

		foreach ($envis as $envi) {
			$button["image"] = "http://koti.mbnet.fi/reaby/xaseco/images/env/" . $envi . ".png";
			$button["function"] = "trackList";
			$button["params"] = "env;" . $envi;
			$menu->addButton($parent, $envi, $button);
		}
	}

	private function generatePlayerSearch_byTmx($parent) {
		$menu = $this->menuPlugin;

		$button["image"] = "http://koti.mbnet.fi/reaby/manialive/images/tmx.png";
		$parent = $menu->addButton($parent, "by TMX", $button);

		$this->generatePlayerSearch_byTmx_type($parent);
		$this->generatePlayerSearch_byTmx_style($parent);
		$this->generatePlayerSearch_byTmx_difficulty($parent);
	}

	private function generatePlayerSearch_byTmx_type($parent) {
		$types = array('Race', 'Puzzle', 'Platform', 'Stunts', 'Shortcut');

		$menu = $this->menuPlugin;

		$button["plugin"] = $this->jbPlugin;

		$button["style"] = "Icons128x128_1";
		$button["substyle"] = "Custom";
		$parent = $menu->addButton($parent, "Tmx Type", $button);

		foreach ($types as $type) {
			$button["style"] = "Icons128x128_1";
			$button["substyle"] = $type;
			$button["function"] = "trackList";
			$button["params"] = "tmx_type;" . $type;
			$menu->addButton($parent, $type, $button);
		}
	}

	private function generatePlayerSearch_byTmx_style($parent) {
		$types = array('Normal', 'Stunt', 'Maze', 'Offroad', 'Laps', 'FullSpeed', 'Lol', 'Tech', 'SpeedTech', 'RPG', 'PresForward');

		$menu = $this->menuPlugin;

		$button["plugin"] = $this->jbPlugin;

		$button["style"] = "Icons64x64_1";
		$button["substyle"] = "Opponents";
		$parent = $menu->addButton($parent, "Tmx Style", $button);

		unset($button["style"]);
		unset($button["substyle"]);

		foreach ($types as $type) {
			$button["function"] = "trackList";
			$button["params"] = "tmx_style;" . $type;
			$menu->addButton($parent, $type, $button);
		}
	}

	//Beginner, Intermediate, Expert, Lunatic

	private function generatePlayerSearch_byTmx_difficulty($parent) {
		$types = array("Beginner" => "Beginner",
			"Intermediate" => "Medium",
			"Expert" => "Hard",
			"Lunatic" => "Extreme");

		$menu = $this->menuPlugin;

		$button["plugin"] = $this->jbPlugin;

		$button["style"] = "BgRaceScore2";
		$button["substyle"] = "Warmup";
		$parent = $menu->addButton($parent, "Tmx Difficulty", $button);

		foreach ($types as $type => $style) {
			$button["style"] = "Icons128x128_1";
			$button["substyle"] = $style;
			$button["function"] = "trackList";
			$button["params"] = "tmx_difficulty;" . $type;
			$menu->addButton($parent, $type, $button);
		}
	}

	private function generatePlayerHelp() {
		$menu = $this->menuPlugin;
		$parent = $menu->findButton(array("Menu", "Help"));

		if (!$parent) {
			$button["style"] = "Icons64x64_1";
			$button["substyle"] = "TrackInfo";

			$parent = $menu->addButton("Menu", "Help", $button);
		}
		$button["style"] = "Icons128x128_1";
		$button["substyle"] = "ProfileVehicle";
		$parent = $menu->addButton($parent, "Jukebox", $button);

		unset($button["style"]);
		unset($button["substyle"]);

		$button["plugin"] = $this->jbPlugin;
		$button["function"] = "trackList";
		$button["params"] = "help";
		$menu->addButton($parent, "Global Help", $button);

		$button["params"] = "cmd";
		$menu->addButton($parent, "/list Commands", $button);
	}

}

?>
