<?php

namespace App\Repositories;

use Core\Db;
use PDO;

class UserRepository {

    private $instance;

    public function __construct() {
        $this->instance = Db::getInstance();
    }

    public function getAll() {
        $query = $this->instance->query('SELECT id, firstname, surname, age, gender FROM users');
        $query->execute();
        return $this->attach($query->fetchAll(PDO::FETCH_ASSOC));
    }

    public function get($id) {
        $query = $this->instance->query('SELECT id, firstname, surname, age, gender FROM users WHERE id = ?');
        $query->execute([$id]);
        return $this->attach([$query->fetch(PDO::FETCH_ASSOC)]);
    }

    private function attach(array $users) {
        foreach($users as &$user) {
            $user['friends'] = $this->getFriends($user);
            $friendsOfFriends = $this->getFriendsOfFriends($user);
            $user['suggested_friends'] = $this->suggestedFriends($friendsOfFriends);
            $user['friends_of_friend'] = $this->removeDuplicatedUsers($friendsOfFriends);
        }

        return $users;
    }

    private function getFriends(array $user) {
        $select = $this->instance->query('SELECT userCon.* FROM users AS u 
        INNER JOIN connections AS c ON u.id = c.user_id 
        INNER JOIN users AS userCon ON c.user_connection_id = userCon.id 
        WHERE u.id = ?');

        $select->execute([$user['id']]);
        return $select->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getFriendsId($userId) {
        $query = $this->instance->query('SELECT userCon.id FROM users AS u 
        INNER JOIN connections AS c ON u.id = c.user_id 
        INNER JOIN users AS userCon ON c.user_connection_id = userCon.id 
        WHERE u.id = ?');
        $query->execute([$userId]);
        return array_map(function($data) {
            return $data['id'];
        }, $query->fetchAll(PDO::FETCH_ASSOC));
    }

    private function getFriendsOfFriends(array $user) {
        $friendsOfFriends = [];
        $friendsId = $this->getFriendsId($user['id']);
        foreach($user['friends'] as $friend) {
            $select = $this->instance->query("SELECT userCon.* FROM users AS u 
            INNER JOIN connections AS c ON u.id = c.user_id
            INNER JOIN users AS userCon ON c.user_connection_id = userCon.id
            WHERE u.id = ? AND c.user_connection_id NOT IN (?) AND c.user_connection_id != ?");

            $select->execute([$friend['id'], $friendsId, $user['id']]);
            $friendsOfFriends = array_merge($friendsOfFriends, $select->fetchAll(PDO::FETCH_ASSOC));
        }

        return $friendsOfFriends;
    }

    public function removeDuplicatedUsers(array $users) {
        $ids = [];

        foreach($users as $key => $user) {
            if(!in_array($user['id'], $ids)) {
                array_push($ids, $user['id']);
                continue;
            }

            unset($users[$key]);
        }

        return $users;
    }

    public function suggestedFriends(array $users) {
        $duplicated = [];
        $uniques = [];
        foreach($users as $key => $user) {
            if(!in_array($user['id'], $uniques)) {
                array_push($uniques, $user['id']);
                continue;
            }

            array_push($duplicated, $user);
            unset($users[$key]);
        }

        return $duplicated;
    }

}