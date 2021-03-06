<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Model\Role
 *
 * @property int $id
 * @property string $name
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Model\Role newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Model\Role newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Model\Role query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Model\Role whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Model\Role whereName($value)
 * @mixin \Eloquent
 */
class Role extends Model
{

    public $timestamps = false;

    public static function admin()
    {
        if (!Role::whereName('admin')->exists()) {
            $role = new Role();
            $role->name = 'admin';
            $role->save();
        }

        return Role::whereName('admin')->firstOrFail();
    }
}
