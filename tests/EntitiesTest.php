<?php

use Laravel\Lumen\Testing\DatabaseTransactions;

class EntitiesTest extends TestCase
{
    use DatabaseTransactions;

    public function testUsers()
    {
        \App\User::whereEmail('gufoes@gmail.com')->delete();
        $this->json('POST', '/users', [
            'email' => 'gufoes@gmail.com',
            'password' => 'asdfasdf',
        ])->seeJson([
            'success' => true,
        ]);

        $this->json('POST', '/users', [
            'email' => 'gufoes@gmail.com',
            'password' => 'asdfasdf',
        ])->seeJson([
            'success' => false,
        ]);

        $this->json('POST', '/sessions', [
            'email' => 'gufoes@gmail.com',
            'password' => 'asdfasdf',
        ])->seeJson([
            'success' => true,
        ]);
    }
}
