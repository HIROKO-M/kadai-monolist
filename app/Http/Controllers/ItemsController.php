<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class ItemsController extends Controller
{


    public function create()
    {
        $keyword = request()->keyword;                                  // フォームから送信される検索ワードを取得。$keywordに入れる
        $items = [];                                                    // $itemsの箱を作る。空の配列を初期化定義
                                                                        // 初期化しないと View 側で $items にアクセスしたときに null となってしまい、エラーが発生します。
                                                                        
        
        if ($keyword){                                                  // 検索キーワードが入力されたら楽天APIを使用して検索
            $client = new \RakutenRws_Client(); 
            $client->setApplicationId(env('RAKUTEN_APPLICATION_ID'));
            
            $rws_response = $client->execute('IchibaItemSearch', [      
                'keyword' => $keyword,                  // 検索キーワード
                'imageFlag' => 1,                       // 画像があるものだけを検索
                'hits' => 20,                           // 取得する商品の数
            ]);
            
            // 扱い易いように Item としてインスタンスを作成する（全ての検索結果は必要ないので保存はしない）
            // $Itemsに値が入る（GETする）のは検索キーワードが入力され自分のモノリストとして追加(Want, Have)されたときにだけ
            foreach ($rws_response->getData()['Items'] as $rws_item) {
                $item = new \App\Item();
                $item->code = $rws_item['Item']['itemCode'];
                $item->name = $rws_item['Item']['itemName'];
                $item->url = $rws_item['Item']['itemUrl'];
                $item->image_url = str_replace('?_ex=128x128', '', $rws_item['Item']['mediumImageUrls'][0]['imageUrl']);
                $items[] = $item;
            }
        }
        
        return view('items.create', [
            'keyword' => $keyword,
            'items' => $items,
        ]);
        
    }


}
