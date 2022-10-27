<?php

use Illuminate\Database\Seeder;
use App\UserService;

class SeederUserService extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        UserService::create([
            'username'=>'bankjatim',
            'password_md5'=>md5('senantiasadalamlindungantuhan')
        ]);
    }
}
