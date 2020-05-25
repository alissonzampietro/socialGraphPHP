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
        return $this->attachTwoStepFriends($this->attachFriends($query->fetchAll(PDO::FETCH_ASSOC)));
    }

    public function get($id) {
        $query = $this->instance->query('SELECT id, firstname, surname, age, gender FROM users WHERE id = ?');
        $query->execute([$id]);
        $data = $this->attachFriends([$query->fetch(PDO::FETCH_ASSOC)]);
        return $this->attachTwoStepFriends($data);
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

    private function attachFriends(array $users) {
        foreach($users as &$user) {
            $select = $this->instance->query('SELECT userCon.* FROM users AS u 
            INNER JOIN connections AS c ON u.id = c.user_id 
            INNER JOIN users AS userCon ON c.user_connection_id = userCon.id 
            WHERE u.id = ?');

            $select->execute([$user['id']]);
            $user['friends'] = $select->fetchAll(PDO::FETCH_ASSOC);
        }

        return $users;
    }

    private function attachTwoStepFriends(array $users) {
        foreach($users as &$user) {
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

            $user['friends_of_friend'] = $friendsOfFriends;
        }

        return $users;
    }

}