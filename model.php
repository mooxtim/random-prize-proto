<?php

class model
{
	protected $db;
	protected $cfg;

	
	public function __construct(mysqli $db, array $cfg) 
	{
		$this->db = $db;
		$this->cfg = $cfg;
		$this->user_id = 1;
	}

	public function get_random()
	{
		$this->db->query("LOCK TABLES `things`, `settings` WRITE");
		// get things list
		$thing_result = $this->db->query("SELECT * FROM `things` WHERE `count` > 0");
		$thing_count = $thing_result->num_rows;
		// get money
		$money_result = $this->db->query("SELECT `value` FROM `settings` WHERE `name` = 'money'");
		list($money_count) = $money_result->fetch_array();
		
		$types = [];
		if ($thing_count > 0 && $money_count >= $this->cfg['points'][1]) {
			$types = [1 => 'points', 2 => 'money', 3 => 'thing'];
		} else if ($thing_count <= 0 && $money_count >= $this->cfg['points'][1]) {
			$types = [1 => 'points', 2 => 'money'];
		} else if ($money_count < $this->cfg['points'][1]) {
			$types = [1 => 'points', 2 => 'thing'];
		} else {
			$types = [1 => 'points'];
		}
		
		$st1 = rand(1, count($types));
// 		$st1 = 2;
		$result = [];
		switch ($types[$st1]) {
			case 'points':
				$result = [
					'type' => $types[$st1],
					'name' => '',
					'count' => rand($this->cfg['points'][0], $this->cfg['points'][1]),
					'thing_id' => 0,
				];
				break;

			case 'money':
				$min = $this->cfg['points'][0];
				$max = $this->cfg['points'][1];
				if ($min > $money_count) {
					$min = $money_count;
				}
				if ($max > $money_count) {
					$max = $money_count;
				}
				$money_rand = rand($min, $max);
				$this->db->query("UPDATE `settings` SET `value` = `value` - {$money_rand} WHERE `name` = 'money'");
				$result = [
					'type' => $types[$st1], 
					'name' => '',
					'count' => $money_rand,
					'thing_id' => 0,
				];
				break;

			case 'thing':
				$st2 = rand(1, $thing_count);
				$thing_result->data_seek($st2 - 1);
				$thing = $thing_result->fetch_assoc();
				$this->db->query("UPDATE `things` SET `count` = `count` - 1 WHERE `thing_id` = {$thing['thing_id']}");
				$result = [
					'type' => $types[$st1], 
					'name' => $thing['name'], 
					'count' => 1,
					'thing_id' => $thing['thing_id'],
				];
				break;
			default:
				$result = false;
		}

		$this->db->query("UNLOCK TABLES");
		
		if ( ! $result) {
			return false;
		}
		
		if ( ! $this->db->query("
			INSERT INTO `prizes` SET
				`time` = ". time() .",
				`type` = '{$result['type']}',
				`name` = '" . $this->db->real_escape_string($result['name']) . "', 
				`count` = {$result['count']},
				`thing_id` = {$result['thing_id']}")) {
			echo $this->db->error; exit;
		}
		$result['prize_id'] = $this->db->insert_id;

		return $result;
		
	}

	public function get_prize($prize_id) 
	{
		$result = $this->db->query("SELECT * FROM `prizes` WHERE `prize_id` = {$prize_id}");
		if ($result->num_rows > 0) {
			return $result->fetch_array();
		} else {
			return false;
		}
	}

	protected function get_prize_for_update($prize_id) 
	{
		$result = $this->db->query("SELECT * FROM `prizes` WHERE `prize_id` = {$prize_id} FOR UPDATE");
		if ($result->num_rows > 0) {
			return $result->fetch_array();
		} else {
			return false;
		}
	}

	public function do_get($prize_id, $user_id) {
		if ( ! $prize = $this->get_prize($prize_id)) { 
			return false;
		} else {
			if ($prize['status'] == 0) {
				switch ($prize['type']) {
					case 'points':
						$this->db->query("UPDATE `user` SET `points` = points + {$prize['count']} WHERE `id` = {$user_id}");
						break;
					case 'money':
						if (!$this->request_bankapi()) {
							return false;
						}
						break;
					case 'thing':
						$this->db->query("INSERT INTO `queue` SET `user_id` = {$user_id}, `thing_id` = {$prize['thing_id']}");
						break;
					default:
						return false;
				}
				$this->db->query("UPDATE `prizes` SET `status` = 1 WHERE `prize_id` = {$prize_id}");
				return true;
			} else {
				return false;
			}
		}
	}
	
	public function do_convert($prize_id, $user_id) {
		if ( ! $prize = $this->get_prize_for_update($prize_id)) { 
			return false;
		} else {
			if ($prize['status'] == 0) {
				$points = 0;
				switch ($prize['type']) {
					case 'money':
						$points = $this->convert($prize['count']);
						$this->db->query("UPDATE `prizes` SET `status` = 2 WHERE `prize_id` = {$prize_id}");
						$this->db->query("UPDATE `user` SET `points` = points + {$points} WHERE `id` = {$user_id}");
						break;
					default:
						return false;
				}
				return $points;
			} else {
				return false;
			}
		}
	}

	public function do_refuse($prize_id) 
	{
		if ( ! $prize = $this->get_prize_for_update($prize_id)) { 
			return false;
		} else {
			if ($prize['status'] == 0) {
				switch ($prize['type']) {
					case 'points':
						break;
					case 'money':
						$this->db->query("UPDATE `settings` SET `value` = `value` + {$prize['count']} WHERE `name` = 'money'");
						break;
					case 'thing':
						$this->db->query("UPDATE `things` SET `count` = `count` + 1 WHERE `thing_id` = {$prize['thing_id']}");
						break;
					default:
						return false;
				}
				$this->db->query("UPDATE `prizes` SET `status` = 3 WHERE `prize_id` = {$prize_id}");
				return true;
			} else {
				return false;
			}
		}
	}

	protected function convert($money) {
		return ceil($money * $this->cfg['ratioMoneyToPoints']);
	}
	
	public function getListPrizes()
	{
		$result = $this->db->query("SELECT * FROM `prizes`");
		$r = [];
		while ($row = $result->fetch_array()) {
			$r[] = $row;
		}
		return $r;
	}
	
	protected function request_bankapi() 
	{
		sleep(3);
		return true;
	}
}


































