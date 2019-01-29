<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract,
                                    AuthorizableContract,
                                    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword;

    /**
     * Userモデルによって使用されるデータベーステーブル。
     */
    protected $table = 'users';

    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password', 'remember_token'];




    public function items()                                     // items テーブルとのリレーション
    {
        return $this->belongsToMany(Item::class)->withPivot('type')->withTimestamps();  //  type=want/haveの両方のItem 一覧を取得する
    }

    public function want_items()
    {
        return $this->items()->where('type', 'want');           // type=want の Item 一覧を取得
    }


    public function want($itemId)                               // Want したときに中間テーブルにレコードを保存
    {
        $exist = $this->is_wanting($itemId);

        if ($exist) {                                           // 既に Want しているかの確認
            return false;                                       // 既に Want していれば何もしない
        } 
        else {                                                
            $this->items()->attach($itemId, ['type' => 'want']);    // 未 Want であれば Want する
            return true;
        }
    }

    public function dont_want($itemId)                          // Want から外すときに使用
    {
        $exist = $this->is_wanting($itemId);

        if ($exist) {                                           // 既に Want しているかの確認
            \DB::delete("DELETE FROM item_user WHERE user_id = ? AND item_id = ? AND type = 'want'", [\Auth::user()->id, $itemId]); // 既に Want していれば Want を外す
        } 
        else {
            return false;                                       // 未 Want であれば何もしない
        }
    }

    public function is_wanting($itemIdOrCode)                   // 既に Want しているかどうかを判定
    {                                                           // $item.id と出力パラメータitemCodeの両方で判断
        if (is_numeric($itemIdOrCode)) {                        // is_numeric()は文字列の整数か判断
            $item_id_exists = $this->want_items()->where('item_id', $itemIdOrCode)->exists();
            return $item_id_exists;
        } else {
            $item_code_exists = $this->want_items()->where('code', $itemIdOrCode)->exists();
            return $item_code_exists;
        }
    }
    
    
    
}
