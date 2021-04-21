<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of usersConfig
 *
 * @author amots
 */
class usersConfig {

    public $userData;

    public function __construct() {
        $this->userData = [
            'amots' => [
                'Password' => '$2a$08$wQSC0lazAwQoebN87yk6gOH2SyVkJ.6jJ3VEavS0WqTegW2zWXb92',
                // 'Password' => '$P$BqD.Fy8IgdZqqtl6h8uszPvvgWSfJM0',
                'User_ID' => '3',
                'level' => '9'],
            'itzik' => [
                'Password'=> '$2a$08$R9kiiBBWjrrgerGTgdxvbe62osAE0tP3Z7uNQNtwGQq3AV.p8E0jy',
                'User_ID' => '4',
                'level' => '8'],
                
        ];
    }

}

?>
