<?php

include 'zzzzzServer.php';

function getGeoCoordinates($str) {
	// Nördlichster Punkt: 54.915833, 8.330833
	// Östlichster Punkt: 48.011667, 17.105556
	// Südlichster Punkt: 46.023604, 7.748607
	// Westlicher Punkt: 46.150017, 5.966648
	$geo_pattern = '/(\-?\d+(\.\d+)?),\s*(\-?\d+(\.\d+)?)/i';
	if (preg_match($geo_pattern, $str, $subpattern)) {
		if (46.023604 <= floatval($subpattern[1]) && floatval($subpattern[1]) <= 54.915833) {
			if (5.966648 <= floatval($subpattern[3]) && floatval($subpattern[3]) <= 17.105556) {
				return $subpattern[1].', '.$subpattern[3];
			}
		}
	}
	return '';
}

function calcUpdateTime($n_fans) {
	$updateTime = time();
	if ($n_fans < 0) {
		$updateTime += 3600*24*6.0;
	}
	elseif ($n_fans < 100) {
		$updateTime += 3600*24*4.0;
	}
	elseif ($n_fans < 500) {
		$updateTime += 3600*24*3.0;
	}
	elseif ($n_fans < 1000) {
		$updateTime += 3600*24*2.5;
	}
	elseif ($n_fans < 2500) {
		$updateTime += 3600*24*2.0;
	}
	elseif ($n_fans < 5000) {
		$updateTime += 3600*24*1.5;
	}
	elseif ($n_fans < 10000) {
		$updateTime += 3600*24*1.0;
	}
	else {
		$updateTime += 3600*24*0.5;
	}
	return $updateTime;
}

class Location {
    
    private $raw_string;
    private $display_location;
    private $country_group;
    
    public function __construct($rawString = '') {
        $this->raw_string = $rawString;
        if ($this->raw_string != '') {
            $this->parseRawLocation();
        }
    }
    
    public function parseRawLocation() {
        global $cities_list;
        foreach ($cities_list as $locationName => $locationData) {
            foreach ($locationData[0] as $locationPattern) {
                if (stripos($this->raw_string, $locationPattern) !== FALSE) {
                    $this->display_location = $locationName;
                    $this->country_group = $locationData[1];
                    return;
                }
            }
        }
        $geoCoordinates = getGeoCoordinates($this->raw_string);
        if ($geoCoordinates != '') {
            $this->display_location = $geoCoordinates;
            $this->country_group = '';
        }
        else {
            $this->display_location = '';
            $this->country_group = '';
        }
    }
    
    public function getDisplayLocation() {
        return $this->display_location;
    }
    
    public function getCountryGroup() {
        return $this->country_group;
    }

}

require 'lib/twitteroauth/twitteroauth.php';
require 'config.php';
define('OAUTH_CALLBACK', '');
$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, OAUTH_TOKEN, OAUTH_SECRET);

if (Chance_Percent(8)) {
	echo 'A';
	$alreadyAdded = array();
	$parameters = array('count' => '75', 'include_rts' => 'true', 'include_entities' => 'true');
	$content = $connection->get('statuses/mentions_timeline', $parameters);
	if (is_array($content)) {
		foreach ($content as $entry) {
			$n_follower = intval($entry->user->followers_count);
			$n_following = intval($entry->user->friends_count);
			$n_fans = $n_follower-$n_following;
			if (!isset($alreadyAdded[$entry->user->id_str])) {
				if ($n_fans >= 900) {
                    $userLocation = new Location($entry->user->location);
					$sql1 = "INSERT INTO _news_twitterers (user_id, screen_name, real_name, image_url, followers, following, fans, location, country, last_update) VALUES (".bigintval($entry->user->id_str).", '".mysql_real_escape_string(trim(strip_tags($entry->user->screen_name)))."', '".mysql_real_escape_string(trim(strip_tags($entry->user->name)))."', '".mysql_real_escape_string(trim(strip_tags($entry->user->profile_image_url)))."', ".$n_follower.", ".$n_following.", ".$n_fans.", '".mysql_real_escape_string($userLocation->getDisplayLocation())."', '".mysql_real_escape_string($userLocation->getCountryGroup())."', ".calcUpdateTime($n_fans).") ON DUPLICATE KEY UPDATE screen_name = '".mysql_real_escape_string(trim(strip_tags($entry->user->screen_name)))."', real_name = '".mysql_real_escape_string(trim(strip_tags($entry->user->name)))."', image_url = '".mysql_real_escape_string(trim(strip_tags($entry->user->profile_image_url)))."', followers = ".$n_follower.", following = ".$n_following.", fans = ".$n_fans.", location = '".mysql_real_escape_string($userLocation->getDisplayLocation())."', country = '".mysql_real_escape_string($userLocation->getCountryGroup())."'";
					mysql_query($sql1) or die(mysql_error());
					echo mysql_insert_id();
				}
				else {
					$sql1 = "DELETE FROM _news_twitterers WHERE user_id = ".bigintval($entry->user->id_str);
					mysql_query($sql1) or die(mysql_error());
					echo mysql_affected_rows();
				}
				$alreadyAdded[$entry->user->id_str] = 1;
			}
			if (isset($entry->entities->user_mentions)) {
				$mentionedUsers = $entry->entities->user_mentions;
				if (is_array($mentionedUsers)) {
					foreach ($mentionedUsers as $mentionedUser) {
						if (!isset($alreadyAdded[$mentionedUser->id_str])) {
							$sql1 = "INSERT INTO _news_twitterers (user_id, screen_name) VALUES (".bigintval($mentionedUser->id_str).", '".mysql_real_escape_string(trim(strip_tags($mentionedUser->screen_name)))."') ON DUPLICATE KEY UPDATE screen_name = '".mysql_real_escape_string(trim(strip_tags($mentionedUser->screen_name)))."'";
							mysql_query($sql1) or die(mysql_error());
							echo mysql_insert_id();
							$alreadyAdded[$mentionedUser->id_str] = 1;
						}
					}
				}
			}
		}
	}
}
elseif (Chance_Percent(85)) {
	echo 'B';
	$sql1 = "SELECT user_id, last_update, banned FROM _news_twitterers ORDER BY last_update ASC LIMIT 0, 90";
	$sql2 = mysql_query($sql1) or die(mysql_error());
	$selectIDs = array();
	$bannedIDs = array();
	while ($sql3 = mysql_fetch_assoc($sql2)) {
		if ($sql3['last_update'] == 1609455599) { continue; }
		$selectIDs[] = $sql3['user_id'];
		if ($sql3['banned'] == 1) { // mark user as banned if necessary
			$bannedIDs[$sql3['user_id']] = 1;
		}
	}
	if (count($selectIDs) > 0) {
        $parameters = array('user_id' => implode(',', $selectIDs));
        $content = $connection->get('users/lookup', $parameters);
        if (is_array($content)) {
			if (is_array($content)) { // XXX remove
				if (is_array($content)) { // XXX remove
					foreach ($content as $entry) {
						$n_id = bigintval($entry->id_str);
						$n_follower = intval($entry->followers_count);
						$n_following = intval($entry->friends_count);
						$n_fans = $n_follower-$n_following;
                        $userLocation = isset($bannedIDs[$n_id]) ? new Location() : new Location($entry->location);
						if ($n_fans >= 900) {
							$sql1 = "UPDATE _news_twitterers SET real_name = '".mysql_real_escape_string(trim(strip_tags($entry->name)))."', image_url = '".mysql_real_escape_string(trim(strip_tags($entry->profile_image_url)))."', followers = ".$n_follower.", following = ".$n_following.", fans = ".$n_fans.", location = '".mysql_real_escape_string($userLocation->getDisplayLocation())."', country = '".mysql_real_escape_string($userLocation->getCountryGroup())."', last_update = ".calcUpdateTime($n_fans)." WHERE user_id = ".$n_id;
							mysql_query($sql1) or die(mysql_error());
							echo mysql_affected_rows();
                            $sql1 = "INSERT IGNORE INTO _news_twitterers_history (user_id, measure_day, followers, following, fans) VALUES (".$n_id.", '".date('Y-m-d')."', ".$n_follower.", ".$n_following.", ".$n_fans.")";
                            mysql_query($sql1) or die(mysql_error());
						}
						else {
							$sql1 = "DELETE FROM _news_twitterers WHERE user_id = ".bigintval($entry->id_str);
							mysql_query($sql1) or die(mysql_error());
							echo mysql_affected_rows();
						}
						// MARK THIS USER AS DONE ANFANG
						$foundPos = array_search(bigintval($entry->id_str), $selectIDs);
						if ($foundPos !== FALSE) {
							unset($selectIDs[$foundPos]);
						}
						// MARK THIS USER AS DONE ENDE
					}
				}
			}
		}
		// MARK USERS WHERE AN ERROR OCCURRED ANFANG
		$nUsersWithErrors = count($selectIDs);
		$setErrorTimeTo = time()+3600*24*5;
		if ($nUsersWithErrors > 0 && $nUsersWithErrors < 75) {
			$postponeUsers1 = "UPDATE _news_twitterers SET last_update = ".$setErrorTimeTo.", location = '', errors_occurred = errors_occurred+1 WHERE user_id IN (".implode(', ', $selectIDs).")";
			mysql_query($postponeUsers1) or die(mysql_error());
			echo mysql_affected_rows();
		}
		// MARK USERS WHERE AN ERROR OCCURRED ENDE
	}
}
else {
	echo 'C';
	$germanWords = array('haben', 'bist', 'eher', 'genug', 'jetzt', 'immer', 'nicht', 'werden', 'einer', 'einem', 'einen', 'durch', 'wurde', 'nach', 'oder', 'aber', 'hatte', 'kann', 'gegen', 'schon', 'auch', 'sich', 'heute', 'dann', 'sind', 'wird', 'wenn', 'habe', 'mehr', 'sein', 'mein', 'gerade', 'meine', 'unter', 'diese', 'wieder', 'keine', 'völlig', 'morgen', 'einfach', 'machen', 'menschen', 'nichts', 'liebe', 'wollen', 'ohne', 'gefunden', 'meinen', 'neuen', 'alten', 'irgendwie', 'lustig', 'daneben', 'warum', 'eigentlich');
	shuffle($germanWords);
    $parameters = array('q' => $germanWords[0], 'result_type' => 'mixed', 'count' => 75);
    $content = $connection->get('search/tweets', $parameters);
	if (is_array($content->statuses)) {
		if (is_array($content->statuses)) { // XXX remove
			if (is_array($content->statuses)) { // XXX remove
				foreach ($content->statuses as $entry) {
					$sql1 = "INSERT INTO _news_twitterers (user_id, screen_name) VALUES (".bigintval($entry->user->id_str).", '".mysql_real_escape_string(trim(strip_tags($entry->user->screen_name)))."') ON DUPLICATE KEY UPDATE screen_name = '".mysql_real_escape_string(trim(strip_tags($entry->user->screen_name)))."'";
					mysql_query($sql1) or die(mysql_error());
					echo mysql_insert_id();
				}
			}
		}
	}
}

echo 'OK';

?>