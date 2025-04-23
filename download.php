<?php
ini_set('display_errors', 0);
ini_set('max_execution_time', 600);
global $node_list;

// -------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------
// -------------------------------NFTDriveDecoder---------------------------------------------
// -------------------------------NAKASHIMA MICHIO--------------------------------------------
// -------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------
//　接続先ノード登録してください。
$node_list = [
    "0-a.symbol-nodes.jp",
    "0-b.symbol-nodes.jp",
    "00.alpaca.symbolist.jp",
    "00.dragon.symbolist.jp",
    "00.elephant.symbolist.jp",
    "00.fushicho.symbolist.jp",
    "00.gorilla.symbolist.jp",
    "00.symsym.info",
    "02.symsym.info",
    "sn3m.newecosym.com",
    "xym-mainnet.11ppm.com",
    "xxx-welcome-to-a-powerful-node.com",
    "207.148.78.8.xym.stir-hosyu.com",
    "7338.work",
    "vmi1560137.contaboserver.net",
    "tryall.symbolmain.net",
    "villhell-symbol-mainnet.net",
    "angel.vistiel-arch.jp",
    "xym0.kalee.land",
    "ik1-107-60488.vs.sakura.ne.jp"
];
// -------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------
//接続先ノードをREST-APIの最大ページ数を設定します。
$restCount = 100;
// -------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------
global $port;
global $http;
global $tx_data;
$tx_data = [];
global $address;
global $packMeta;
global $thum;
$thum = null;
global $openSeaMeta;
global $filetype;

//ブラックリスト
function isBlackList($id)
{

    $curl = curl_init("https://nftdrive-explorer.info/black_list/");

    // リクエストのオプションをセットしていく
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET'); // メソッド指定
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 証明書の検証を行わない
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // レスポンスを文字列で受け取る

    // レスポンスを変数に入れる
    $response = curl_exec($curl);


    $response = json_decode($response);


    // print_r($response);

    for ($i = 0; $i < count($response); $i++) {

        if ($response[$i][1] == $id) {
            return true;
        }


    }

    return false;

}

if (isset($_GET["id"])) {

    if (isBlackList($_GET["id"])) {

        print "
        This data is blacklisted and cannot be decoded. Please contact NFTDrive if you have any problems.";

        exit;

    }
}

if (isset($_GET["address"])) {



    if (isBlackList($_GET["address"])) {

        print "
        This data is blacklisted and cannot be decoded. Please contact NFTDrive if you have any problems.";

        exit;

    }
}
function base32_encode($input, $padding = true)
{
    $map = array(
        'A',
        'B',
        'C',
        'D',
        'E',
        'F',
        'G',
        'H', //  7
        'I',
        'J',
        'K',
        'L',
        'M',
        'N',
        'O',
        'P', // 15
        'Q',
        'R',
        'S',
        'T',
        'U',
        'V',
        'W',
        'X', // 23
        'Y',
        'Z',
        '2',
        '3',
        '4',
        '5',
        '6',
        '7', // 31
        '='  // padding char
    );

    if (empty($input))
        return "";
    $input = str_split($input);
    $binaryString = "";
    for ($i = 0; $i < count($input); $i++) {
        $binaryString .= str_pad(base_convert(ord($input[$i]), 10, 2), 8, '0', STR_PAD_LEFT);
    }
    $fiveBitBinaryArray = str_split($binaryString, 5);
    $base32 = "";
    $i = 0;
    while ($i < count($fiveBitBinaryArray)) {
        $base32 .= $map[base_convert(str_pad($fiveBitBinaryArray[$i], 5, '0'), 2, 10)];
        $i++;
    }
    if ($padding && ($x = strlen($binaryString) % 40) != 0) {
        if ($x == 8)
            $base32 .= str_repeat($map[32], 6);
        else if ($x == 16)
            $base32 .= str_repeat($map[32], 4);
        else if ($x == 24)
            $base32 .= str_repeat($map[32], 3);
        else if ($x == 32)
            $base32 .= $map[32];
    }
    return $base32;
}

if (isset($_GET["address"]) || isset($_GET["id"])) {


    global $address;


    if (isset($_GET["address"])) {

        global $address;

        $address = str_replace("-", "", $_GET["address"]);
    } else {

        global $address;

        $address = str_replace("-", "", $_GET["id"]);

    }


    $strcount = strlen($address);
    // print $strcount;

    //アドレスかMOSAICか？
    if ($strcount == 16) {

        // １台以上あるときシャッフル
        if (count($node_list) > 1) {
            shuffle($node_list);
        }




        global $port;
        $port = 3000;
        global $http;
        $http = "http://" . $node_list[0] . ":" . $port;




        $curl = curl_init($http . "/mosaics/" . $address);

        // リクエストのオプションをセットしていく
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET'); // メソッド指定
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 証明書の検証を行わない
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // レスポンスを文字列で受け取る

        // レスポンスを変数に入れる
        $response = curl_exec($curl);

        // curlの処理を終了
        curl_close($curl);

        // print $response;

        $m_j = json_decode($response, true);

        $base32 = $m_j["mosaic"]["ownerAddress"];

        // print$base32;

        $asd = base32_encode(hex2bin($base32));

        // print"[".$asd."]";

        global $address;

        $address = str_replace('=', '', $asd);






    } else {


        if (isset($_GET["address"])) {



            global $address;
            $address = str_replace("-", "", $_GET["address"]);

        } else {
            global $address;

            $address = str_replace("-", "", $_GET["id"]);

        }






    }

}

if ($address[0] == "N") {



} else {

    global $restCount;
    $restCount = 100;
    $node_list = [
        "test02.xymnodes.com",
        "test01.xymnodes.com",
        "sym-test-10.opening-line.jp",
        "sym-test-04.opening-line.jp",
        "sym-test-03.opening-line.jp",
        "sym-test-10.opening-line.jp",
        "sym-test-03.opening-line.jp"
    ];

}

global $port;
$port = 3000;
global $http;
$http = "http://" . $node_list[0] . ":" . $port;
$symbol_node = $http . "/node/info";
$curl = curl_init($symbol_node);
// リクエストのオプションをセットしていく
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET'); // メソッド指定
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 証明書の検証を行わない
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // レスポンスを文字列で受け取る
$response = curl_exec($curl);
curl_close($curl);
$all_count = 0;
global $header;
global $data;
global $js;
$js = [];
function ag_tx($hash)
{


    global $counter;
    $counter = 0;
    global $header;
    global $http;
    global $tx_data;
    $tx_data = "";
    $curl = curl_init($http . "/transactions/confirmed/" . $hash);

    // リクエストのオプションをセットしていく
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET'); // メソッド指定
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 証明書の検証を行わない
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // レスポンスを文字列で受け取る

    // レスポンスを変数に入れる
    $response = curl_exec($curl);

    // curlの処理を終了
    curl_close($curl);


    $tx_ag = json_decode($response, true);

    foreach ($tx_ag as $value) {


        if (isset($value["transactions"])) {

            foreach ($value["transactions"] as $tx) {

                global $counter;

                if (isset($tx["transaction"])) {
                    global $counter;
                    //MOSAIC作成と分岐
                    if ($tx["transaction"]["type"] == 16724) {
                        //メッセージがあるとき

                        if (isset($tx["transaction"]["message"])) {
                            global $counter;

                            if ($counter == 0) {

                                global $header;
                                $asd = hex2bin($tx["transaction"]["message"]);
                                $header = intval(trim($asd));


                            } else {
                                //ヘッダーが０番の時は５番目からとる   
                                global $header;
                                //   print"header=".$header.";";\

                                if ($header == 0) {
                                    //STEPさせる15行              
                                    // print"kaunt".$counter."@";


                                    //OpenSeaだったら

                                    //OpenSeaだったら
                                    if ($counter == 5) {

                                        global $openSeaMeta;
                                        $openSeaMeta = hex2bin($tx["transaction"]["message"]);



                                    }



                                    //拡張子を取得。
                                    if ($counter == 10) {

                                        global $filetype;
                                        $filetype = trim(hex2bin($tx["transaction"]["message"]));


                                    }






                                    if ($counter < 15) {
                                        global $js;


                                        $js = array_merge($js, [hex2bin($tx["transaction"]["message"])]);





                                    }







                                    global $counter;

                                    if ($counter >= 15) {

                                        global $header;
                                        global $counter;
                                        // print"<pre>";
                                        // print"counter:".$counter."/header:".$header.":".hex2bin($tx["transaction"]["message"]);
                                        // print"</pre>";

                                        global $tx_data;
                                        $tx_data = $tx_data . hex2bin($tx["transaction"]["message"]);

                                    } else {

                                        //ヘッダー０番かつデータフィールど以下。
                                        //何もしない

                                    }





                                } else {


                                    global $counter;

                                    //ヘッダー番号が１番以外
                                    if ($counter > 0) {

                                        // print"ヘッダーが一番以外";
                                        // global $counter;
                                        // print"<pre>";
                                        // print"counter:".$counter."@/header:".$header.":".hex2bin($tx["transaction"]["message"]);
                                        // print"</pre>";

                                        global $tx_data;

                                        $tx_data = $tx_data . hex2bin($tx["transaction"]["message"]);


                                    }


                                }
                            }





                        } else {
                            // print"NO-MESSAGE<br>";
                        }
                    }//MOSAIC作成



                }//トランザクションがある

                //内包トランザクションのカウントを進める
                global $counter;
                $counter++;

            }


            //ここでヘッダー付きの配列に挿入していく。


            global $counter;
            $counter = 0;
            // print "count".$counter."<br>";
            global $data;
            global $tx_data;
            global $header;
            $data[$header] = $tx_data;
            //1AGTXごとに配列初期化
// $tx_data="";
// print "<pre>";
// print_r($data);
// print "</pre>";


        }//内包トランザクションがある場合  



    }



}

//全履歴取得
function get_rireki($address, $pagesize = 2000, $page)
{
    global $tx_data;

    global $http;



    global $restCount;


    $curl = curl_init($http . "/transactions/confirmed?address=" . $address . "&pageSize=" . $restCount . "&pageNumber=" . $page);

    // リクエストのオプションをセットしていく
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET'); // メソッド指定
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 証明書の検証を行わない
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // レスポンスを文字列で受け取る

    // レスポンスを変数に入れる
    $response = curl_exec($curl);

    // curlの処理を終了
    curl_close($curl);


    $tx_cash = json_decode($response, true);
    // var_dump($tx_cash);



    //トランザクションタイプの確認16705
    foreach ($tx_cash as $row) {

        foreach ($row as $tx) {

            //トランザクションタイプがあるかチェック
            if (isset($tx["transaction"]["type"])) {
                //16705アグリゲートトランザクションかチェック
                if ($tx["transaction"]["type"] == 16705) {

                    ag_tx($tx["meta"]["hash"]);

                }
            }
        }




        //ページがまだある
        // print"<pre>";
        // print_r($row);
        // print"</pre>";

        // print COUNT($row);
// 次のページがまだる

        global $restCount;

        if (COUNT($row) == $restCount) {

            global $restCount;

            get_rireki($address, $restCount, $page++);

        }



        // if(count($tx)==100){

        //     // get_rireki($address,$row["pageNum"]+1);

        // }

    }

    // print$tx_data;
}

global $aaa;
$aaa = "";
// トランザクションサーチ
global $restCount;
get_rireki($address, $restCount, 1);

//並び替え


ksort($data);

foreach ($data as $row3) {
    // print"<pre>";
//     var_dump($row3);
// print"</pre>";
    foreach ((array) $row3 as $ed) {
        // print"<pre>";
        // print strlen($ed);
        // print"</pre>";
        global $aaa;

        $aaa = $aaa . $ed;

    }

}

$bbb = explode(",", $aaa);
$type = substr($bbb[0], 6);
$type = substr($type, 0, -7);


if ($type == "mid") {

    $type = "audio/midi";
}

if ($type == "midi") {

    $type = "audio/midi audio/x-midi";
}



if (mb_substr($js[0], 1, 1) == "N" || mb_substr($js[0], 1, 1) == "T") {

    //キャッシュ名
    global $id;
    $id = uniqid();
    global $aaa;

    global $file_tyle;
    $file_tyle = null;

    if (strpos($aaa, "base64") !== false) {
        //そのたのファイルの時
        if (strpos($aaa, "octet-stream") !== false) {

            global $js;
            foreach ($js as $key => $value) {
                if (strlen($value < 4)) {
                    if (strpos($value, "glb") !== false) {
                        global $file_tyle;
                        $file_tyle = "3dmodel-file.glb";
                        header('Content-Type: application/glb; filename=' . $file_tyle . ';charset=UTF-8');
                        break;
                    }

                    if (strpos($value, "gltf") !== false) {
                        //    print "その他のファイルです";
                        global $file_tyle;
                        $file_tyle = "3dmodel-file.gltf";
                        break;
                    }

                    if (strpos($value, "bvh") !== false) {
                        global $file_tyle;
                        $file_tyle = "3d-motion-file.bvh";
                        break;
                    }
                    if (strpos($value, "packfiles") !== false) {
                        global $file_tyle;
                        global $js;
                        $file_tyle = "nftd";
                        break;
                    }
                }
            }
        }

        try {
            //code...         

            file_put_contents($id, base64_decode($bbb[1]));
            $image = file_get_contents($id);


        } catch (\Throwable $th) {
            // throw $th;

        }
        //ATNFTの処理
        if ($image[0] == "h") {
            if (strpos($image, 'http') !== false) {
                global $id;
                $data = file_get_contents($image);
                file_put_contents($id . "2", $data);
                $image2 = file_get_contents($id . "2");
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $type2 = $finfo->file($id . "2");
                header('Content-Type: ' . $type2, false);
                print $image2;
                unlink($id . "2");
                unlink($id);

            } else {

                header('Content-Type: ' . $type, false);
                print $image;
                unlink($id);

            }

        } else {

            //NULLじゃなかったら例外ファイル
            if ($file_tyle == "3d-motion-file.bvh") {

            //3Dモデルファイル
                header('Content-Type: application/bvh; filename=' . $file_tyle . ';charset=UTF-8');
                print trim($image);
                unlink($id);

            } else {

                //オーディオ系のダウンロード禁止にする。
                // NFTDriveの時
                if ($_SERVER['HTTP_HOST'] == "nftdrive-explorer.info" || $_SERVER['HTTP_HOST'] == "nft-drive" || $_SERVER['HTTP_HOST'] == "nftdrive-ex.net") {

                    if ($type == "audio/mpeg" || $type == "video/mp4") {

                        header('Content-Type: text/html');

                    } else {

                        header('Content-Type: ' . $type, false);
                    }

                } else {

                    header('Content-Type: ' . $type, false);

                }


                if ($file_tyle == 'nftd') {
                    global $packMeta;
                    $dsa = $packMeta . $file_tyle;
                    header('Content-Disposition: attachment; filename=' . $dsa);

                } else if ($file_tyle !== null) {

                    header('Content-Disposition: attachment; filename=' . $file_tyle);

                }

                if ($_SERVER['HTTP_HOST'] == "nftdrive-explorer.info" || $_SERVER['HTTP_HOST'] == "nft-drive" || $_SERVER['HTTP_HOST'] == "nftdrive-ex.net") {
                    //オーディオはダウンロード禁止サムネ
                    if ($type == "video/mp4") {
                        //ビデオはサムネなし
                        $stype = explode("/", $type);
                        print '<html><head><meta name="viewport" content="width=device-width"><style media="screen" type="text/css">@keyframes fade-in{0% {opacity: 0} 100% {opacity: 1.0}}' . '@keyframes fade-out{0% {opacity: 1.0}100% {opacity: 0}}</style></head><body style=background:black; ><div style="width:100%;height:100%;display:flex;justify-content:center;align-items:center;"><video controls="" style="max-width:100%;" autoplay="" name="media" controlsList="nodownload" oncontextmenu="return false;"><source type="' . $stype[0] . '/' . $stype[1] . '" src="data:' . $type . ";base64," . base64_encode($image) . '"></video></div></body></html>';

                    } elseif ($type == "audio/mpeg") {
                        // オーディオはサムネありとなし。
                        global $openSeaMeta;
                        $openSeaMeta = preg_replace('/[[:cntrl:]]/', '', $openSeaMeta);
                        $ssssss = json_decode(stripslashes($openSeaMeta), true);
                        if (isset($ssssss["animation_url"])) {
                            $domain = $_SERVER['HTTP_HOST'];
                            $stype = explode("/", $type);
                            print '<html><head><meta name="viewport" content="width=device-width"><style media="screen" type="text/css">@keyframes fade-in{0% {opacity: 0} 100% {opacity: 1.0}}' .
                                '@keyframes fade-out{0% {opacity: 1.0}100% {opacity: 0}}</style></head><body style=background:black; ><div style="width:100%;height:100%;width: 100%;height: 100%;display: flex;justify-content: center;align-items: center;flex-direction: column;"><div><img style="max-width:100vw;max-height:80vh;" src="http://' . $domain . '/download.php?address=' . $ssssss["animation_url"] . '"></div><div><video style="margin-top:-100px;" controls="" autoplay="" name="media" controlsList="nodownload" oncontextmenu="return false;"><source type="' . $stype[0] . '/' . $stype[1] . '" src="data:' . $type . ";base64," . base64_encode($image) . '"></video></div></div></body></html>';

                        } else {
                            $stype = explode("/", $type);
                            print '<html><head><meta name="viewport" content="width=device-width"><style media="screen" type="text/css">@keyframes fade-in{0% {opacity: 0} 100% {opacity: 1.0}}' .
                                '@keyframes fade-out{0% {opacity: 1.0}100% {opacity: 0}}</style></head><body style=background:black; ><div style="width:100%;height:100%;display:flex;justify-content:center;align-items:center;"><video controls="" autoplay="" name="media" controlsList="nodownload" oncontextmenu="return false;"><source type="' . $stype[0] . '/' . $stype[1] . '" src="data:' . $type . ";base64," . base64_encode($image) . '"></video></div></body></html>';

                        }

                    } else {
                        //ジェネラティブ
                        //ここでNFGLileか調べる        
                        if (strpos($image, 'NFTDriveGeneratibve.v.0.1.0') !== false) {
                            //ファイルを取り出す
                            $geneArray = [];
                            $genedata = explode("@", $image);    
                            // チップのサイズ。
                            $new_rect = $genedata[1];
                            //チップの数縦横
                            $mapX = $genedata[3];
                            $mapY = $genedata[2];
                            $src_image = imagecreatefrompng($genedata[6]);
                            // 切り抜いた画像の貼付け先リソース(正方形)を確保
                            for ($i = 0; $i < $mapX; $i++) {
                                for ($i2 = 0; $i2 < $mapY; $i2++) {
                               
                                    $dst_image = imagecreatetruecolor($new_rect, $new_rect);
                                    //透過処理
                                    imagealphablending($dst_image, false);
                                    imagesavealpha($dst_image, true);
                                    //ここまで透過処理
                                    //切り取り
                                    imagecopyresampled($dst_image, $src_image, 0, 0, $new_rect * $i2, $new_rect * $i, $new_rect, $new_rect, $new_rect, $new_rect);
                                    // 結果を保存
                                    //マップチップをBase64化
                                    ob_start();
                                    imagepng($dst_image);
                                    // Capture the output and clear the output buffer
                                    $imagedata = ob_get_clean();
                                    //配列にプッシュ
                                    $geneArray[] = ["$i" . "-" . "$i2", "data:image/png;base64," . base64_encode($imagedata)];

                                   

                                }


                            }

                            //ジェネ配列データ
                            $mkImg = [];
                            //ジェネ画像データ
                            $imgFns = [];
                            // 空の画像を作成する
                            $img = imagecreatetruecolor($new_rect, $new_rect);

                            // 背景を透明にする
                            imagecolortransparent($img, imagecolorallocate($img, 0, 0, 0));
                            // 定義データ取り出し
                            $geneJson = json_decode($genedata[5]);
                            //定義データ番号
                            if (isset($_GET["b"])) {
                                $te = $_GET["b"];
                            } else {
                                $te = 0;
                            }
                            $config = $geneJson[$te];

                            for ($i = 0; $i < $mapX; $i++) {
                                for ($i3 = 0; $i3 < count($geneArray); $i3++) {

                                    if ($config[$i] . "-" . $i == $geneArray[$i3][0]) {

                                        $imgFns[$i] = $geneArray[$i3][1];

                                    }

                                }


                            }



                            // シンプルな画像合成
                            foreach ($imgFns as $fn) {
                                $img2 = imagecreatefrompng($fn); // 合成する画像を取り込む

                                // 合成する画像のサイズを取得
                                $sx = imagesx($img2);
                                $sy = imagesy($img2);

                                imageLayerEffect($img, IMG_EFFECT_ALPHABLEND);// 合成する際、透過を考慮する
                                imagecopy($img, $img2, 0, 0, 0, 0, $sx, $sy); // 合成する

                                imagedestroy($img2); // 破棄


                            }

                            header('Content-Type: image/png');
                            $width = imagesx($img);
                            $hight = imagesy($img);
                            $image3 = imagecreatetruecolor(500, 500); // サイズを指定して新しい画像のキャンバスを作成
                            imagecolortransparent($image3, imagecolorallocate($image3, 0, 0, 0));
                            imageLayerEffect($img, IMG_EFFECT_ALPHABLEND);// 合成する際、透過を考慮する
                            // // 画像のコピーと伸縮
                            Imagecopyresampled($image3, $img, 0, 0, 0, 0, 500, 500, $width, $hight);

                            // // コピーした画像を出力する

                            imagepng($image3);
                            imagedestroy($image3);
                            imagedestroy($img);

                        } else {
                            print $image;
                        }

                    }


                } else {

                    print $image;

                }

                unlink($id);

            }

        }



    } else {

        header('Content-Type: text/plain; charset=UTF-8', false);
        global $aaa;
        print $aaa;

    }

} else {
    global $address;
}

?>