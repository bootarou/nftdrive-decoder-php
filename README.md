# 株式会社NFTDrive 公式デコーダー


このリポジトリには、**株式会社NFTDrive** が提供する Symbolブロックチェーン上のデータを読み解く  
**PHP製デコーダー（download.php）** が含まれています。

これらのプログラムは **自由に利用・改編** することができます。

これらのプログラムを含むデコーダーを公開するには下記の利用規約を確認してください。
[利用規約](https://nft-drive.localinfo.jp/posts/23874701)

[株式会社NFTDrive](https://nftdrive.net)



---

## 📥 ダウンロード

▶ PHP版：  
[🔗 Download `download.php`](./download.php)  
（※ローカル環境に保存し、Webサーバーに設置してください）

---

## ⚙ 設置方法

1. `download.php` を Webサーバー上にアップロードします（例：Apache、XAMPPなど）。

---

## 🔧 設定手順

### 1. ノードの設定

`download.php` を開いて、以下のように使用するノードのURLを `$node_list` に追加してください。

```php
$node_list = [
  "https://example-node1.com:3001",
  "https://example-node2.net:3001"
];


```

### 2. APIの最大ページ数

`$restCount`に接続先ノードの最大ページ数を設定します。
通常は１００です。

```php

$restCount=100;

```

## 使い方

パラメーターにMOSAICもしくはデータアドレスを指定します。
画像などMIMEヘッダー付きの通常のデータとして返します。

① download.php?id={MOSAIC-ID}

② download.php?address={データアドレス}

