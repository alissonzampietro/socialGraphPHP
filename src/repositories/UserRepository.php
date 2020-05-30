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
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get($id) {
        $query = $this->instance->query('SELECT id, firstname, surname, age, gender FROM users WHERE id = ?');
        $query->execute([$id]);
        return $this->attach([$query->fetch(PDO::FETCH_ASSOC)]);
    }

    public function graph() {
        $query = $this->instance->query('SELECT id, firstname, surname, age, gender FROM users');
        $query->execute();
        return $this->attach($query->fetchAll(PDO::FETCH_ASSOC), true);
    }

    private function attach(array $users = [], $onlyFriends = false) {
        foreach($users as &$user) {
            $user['friends'] = $this->getFriends($user);
            $friendsId = array_map(function($user) {
                return $user['id'];
            }, $user['friends']);

            if(!$onlyFriends) {
                $friendsOfFriends = $this->getFriendsOfFriends($user);
                $user['suggested_friends'] = $this->suggestedFriends($friendsOfFriends, $friendsId);
                $user['friends_of_friend'] = $this->removeDuplicatedUsers($friendsOfFriends);
                $user['suggested_places'] = $this->suggestedPlaces($user);
            }
            
        }

        return $users;
    }

    private function getConnections(array $user) {
        $select = $this->instance->query('SELECT userCon.* FROM users AS u 
        INNER JOIN connections AS c ON u.id = c.user_id 
        INNER JOIN users AS userCon ON c.user_connection_id = userCon.id 
        WHERE u.id = ?');

        $select->execute([$user['id']]);
        return $select->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getFriends(array $user) {
        $select = $this->instance->query('SELECT userCon.* FROM users AS u 
        INNER JOIN connections AS c ON u.id = c.user_id 
        INNER JOIN users AS userCon ON c.user_connection_id = userCon.id 
        WHERE u.id = ?');

        $select->execute([$user['id']]);
        return $select->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getFriendsOfFriends(array $user) {
        $friendsOfFriends = [];
        foreach($user['friends'] as $friend) {
            $select = $this->instance->query("SELECT userCon.* FROM users AS u 
            INNER JOIN connections AS c ON u.id = c.user_id
            INNER JOIN users AS userCon ON c.user_connection_id = userCon.id
            WHERE u.id = :friendId AND c.user_connection_id NOT IN (
                SELECT userCon.id FROM users AS u 
                INNER JOIN connections AS c ON u.id = c.user_id 
                INNER JOIN users AS userCon ON c.user_connection_id = userCon.id 
                WHERE u.id = :userId
            ) AND c.user_connection_id != :userId");
            
            $select->bindParam(':friendId', $friend['id']);
            $select->bindParam(':userId', $user['id']);
            $select->execute();
            
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

    public function suggestedFriends(array $users, array $friendsId) {
        $duplicated = [];
        $uniques = [];
        foreach($users as $key => $user) {
            if(!in_array($user['id'], $uniques)) {
                array_push($uniques, $user['id']);
                continue;
            }

            if(in_array($user['id'], $friendsId)) {
                continue;
            }

            array_push($duplicated, $user);
            unset($users[$key]);
        }

        return $duplicated;
    }

    public function suggestedPlaces(array $user) {

        $query = "SELECT ci.name, uc.percentual FROM users AS u 
        INNER JOIN connections AS c ON u.id = c.user_id 
        INNER JOIN users AS userCon ON c.user_connection_id = userCon.id
        INNER JOIN users_cities uc ON uc.user_id = userCon.id AND uc.city_id NOT IN (SELECT uc2.city_id FROM users_cities uc2 where uc2.user_id = :userId)
        INNER JOIN cities ci ON uc.city_id = ci.id
        WHERE u.id = :userId
        GROUP BY uc.city_id
        ORDER BY uc.percentual DESC
       	LIMIT 3;";

        $select = $this->instance->prepare($query);

        $select->bindParam(':userId', $user['id']);

        $select->execute();
        $result = $select->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

}