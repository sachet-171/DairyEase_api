<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user_list = Permission::create(['name'=>'users.list']);
        $user_view = Permission::create(['name'=>'users.view']);
        $user_create = Permission::create(['name'=>'users.create']);
        $user_update = Permission::create(['name'=>'users.update']);
        $user_delete = Permission::create(['name'=>'users.delete']);

        $admin_role = Role::create(['name'=>'admin']);
        $admin_role->givePermissionTo([
            $user_create,
            $user_list,
            $user_view,
            $user_update,
            $user_delete
        ]);

        $admin = User::create([
            'name' => 'Admin',
            'email'=>'dairyease17@gmail.com',
            'contact'=>'9866300007',
            'is_admin'=>'1',
            'email_verified_at' => now(),
            'address'=>'tandi',
            'password'=>bcrypt('password')
        ]);
        $admin-> assignRole($admin_role);
        $admin->givePermissionTo([
            $user_create,
            $user_list,
            $user_view,
            $user_update,
            $user_delete
        ]);
        $user = User::create([
            'name' => 'User',
            'email'=>'jyotichapagain29@gmail.com',
            'contact'=>'9866300009',
            'is_admin'=>'0',
            'email_verified_at' => now(),
            'address'=>'tandi',
            'password'=>bcrypt('password')
        ]);

        $user_role = Role::create(['name'=>'user']);


        $user-> assignRole($user_role);
        $user->givePermissionTo([
            $user_list,
           
        ]);
        $user_role->givePermissionTo([
            $user_list,
           
        ]);
        foreach ($user as $key => $value) {
            User::create($value);
        }
    }
}
