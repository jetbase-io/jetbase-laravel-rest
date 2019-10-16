<?php

namespace App\Console\Commands\Users;

use App\Model\Role;
use App\Model\User;
use Illuminate\Console\Command;

class Create extends Command {
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'users:create
                          {--F|first_name= : User first name}
                          {--L|last_name= : User last name}
                          {--E|email= : User email}
                          {--P|password= : User password}
                          {--A|admin : Assign \'admin\' role}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Create an user';

  /**
   * Create a new command instance.
   *
   * @return void
   */
  public function __construct() {
    parent::__construct();
  }

  /**
   * Execute the console command.
   *
   * @return void
   */
  public function handle() {
    $first_name = $this->option('first_name');
    $last_name = $this->option('last_name');
    $email = $this->option('email');
    $password = $this->option('password');
    $isAdmin = $this->option('admin');
    //dd(compact('first_name', 'last_name', 'email', 'password', 'isAdmin'));

    $user = new User();
    $user->first_name = $first_name;
    $user->last_name = $last_name;
    $user->email = $email;
    $user->password = bcrypt($password);
    if ($isAdmin) {
      $user->role_id = $this->adminRole()->id;
    }
    $user->save();
    $this->comment('user created');
  }

  private function adminRole(): Role {
    $role = Role::whereName('admin')->first();

    if (!$role) {
      $role = new Role();
      $role->name = 'admin';
      $role->save();
    }

    return $role;
  }
}
