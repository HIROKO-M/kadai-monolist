<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Item;

class ItemsUserController extends Controller
{

    public function want()
    {
        $itemCode = request()->itemCode;

        
        //楽天APIを使用して　itemCode から商品を検索
        $client = new \RakutenRws_Client();                         // クライアント作成
        $client->setApplicationId(env('RAKUTEN_APPLICATION_ID'));   // アプリID設定
        $rws_response = $client->execute('IchibaItemSearch', [      // オプションを付けて検索を実行
                'itemCode' => $itemCode,                            // itemCodeで検索を実行
        ]);
        $rws_item = $rws_response->getData()['Items'][0]['Item'];
        
        
        // Item 保存　or　検索（見つかると作成せずにそのインスタンスを取得する）
        $item = Item::firstOrCreate([
            'code' => $rws_item['itemCode'],
            'name' => $rws_item['itemName'],
            'url' => $rws_item['itemUrl'],
            
            // 画像のURLの最後に?_ex128x128　とついてサイズが決められてしまうので取り除く
            'image_url' => str_replace('?_ex=128x128', '', $rws_item['mediumImageUrls'][0]['imageUrl']),
            ]);
            
            \Auth::user()->want($item->id);                         //ログインユーザと商品の間に Want の関係を保存
            
            return redirect()->back();
            
    }
    
    public function dont_want()                                     // Want されていた商品を Want リストから除外します。
    {
        $itemCode = request()->itemCode;
        
        if(\Auth::user()->is_wanting($itemCode)){                   // ログインユーザで$itemCodeがあるなら
            $itemId = Item::where('code', $itemCode)->first()->id;  // $itemIdへItemの最初のidのcodeを入れる
            \Auth::user()->dont_want($itemId);                      // ログインユーザと商品の間に dont_Want の関係を保存
        }
        
        return redirect()->back();
    }
    
}
