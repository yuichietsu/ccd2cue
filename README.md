## 概要

RetroArchのためにPCからFire TV Stickにゲームを転送するためのスクリプトです。   
次のような問題点を解決するためのスクリプトです。

- 手動でゲームをコピーするのは面倒くさい
- PCのディレクトリをsmabaでマウントできなさそう
- rsyncは使えなさそう
- Fire TV StickのストレージにはPCに保存している全部のゲームは入らない

スクリプト内部では`rm -rf`などのコマンドを使うのでシステムの破壊をする可能性があります。  
ご注意ください。

## 前提条件

### 転送元(PC)

スクリプトを実行するPCでは、次のコマンドを使用しています。

- adb
- find
- rm
- zip (オプション)
  - PCのzipを展開してから転送したい場合に必要(FDDとかCD系は展開してないとうまく動かない)
  - PCの7zをzipに変換して転送したい場合に必要(Lemuroidがzipのみサポートしているので実装してみた)
- 7z (オプション)
  - PCの7zを展開してから転送したい場合に必要(FDDとかCD系は展開してないとうまく動かない)

転送元のスクリプトは、WindowsのWLSで動作させる前提で作っています。

### 転送先(Fire TV Stick)

- 開発者オプションが有効になっている
- 転送元のPCを接続を許可する

## 使用方法

使い方の一例を記載します。

```php
$sync = new \Menrui\AdbSync(
    '/usr/bin/adb',         // adbのパス
    '192.168.11.44:5555',   // Fire TV Stick
);

$sync->srcPath = '/mnt/d/files/roms/rebuild';                                // PCの転送元のディレクトリ、サブディレクトリにmame/とかnes/とかある
$sync->dstPath = '/storage/B42F-0FFA/Android/data/com.retroarch/files/ROM';  // Fire TV Stickの転送先のディレクトリ

$sync->sync(
    [
        'mame'          => 'rand:4g',       // 4GBを超えない範囲でランダムに選び出して送る
        'nes'           => 'full',          // 全部送る
        'psp'           => 'rand:1g,ext',   // 転送元の7z/zipファイルを展開してから、展開後のサイズで1GBを超えない範囲でランダムで送る
    ],
);
```

## 設定

### `\Menrui\AdbSync`のメンバプロパティ

#### srcPath : string
転送元のPCのディレクトリ(必須)

#### dstPath : string
転送先のFire TV Stickのディレクトリ(必須)

#### statesPaths : array
Fire TV StickのRetroArchのstateファイルの保存先のディレクトリ。セーブしたゲームはFire TV Stickから削除しないようにロックする判定に使用する。

#### lockDays : int
セーブしたゲームをFire TV Stickから削除しない日数(デフォルト14)

#### retryCount : int
adbでコマンドを実行するとき、たまに失敗するみたいなので、再試行する回数(デフォルト5)

#### retrySleep : int
adbのコマンドの再試行をする前に待機する秒数(デフォルト60)

### `sync()`メソッドの引数

連想配列のキーはsrcPathのサブディレクトリ、バリューは転送モードを指定します。

#### 転送モード

##### full
PCのゲームを全て転送するモード  
PCにないゲームはFire TV Stickから削除されます。  
指定可能なオプション: ext, lock, zip

##### rand
PCのゲームをランダムで選出して転送するモード  
ランダムで選ばれた以外のゲームはFire TV Stickから削除される。  
指定可能なオプション: (ゲーム数), (サイズ), ext, lock, zip

##### filter
ファイル名に指定した文字列を含むゲームのみを転送するモード  
文字列を含まないゲームはFire TV Stickから削除される。
指定可能なオプション: (フィルタ文字列), ext, lock, zip

#### 転送モードオプション

##### ext
PCの7z, zipファイルを展開してから転送します。  
拡張子なし名前のディレクトリの下にファイル(.cue, .binなど)を展開します。

##### lock
通常、転送対象ではないゲームはFire TV Stickから削除されるが、ステートセーブしたゲームは削除しないようにする。  
デフォルトでセーブしてから14日間保護する。

##### zip
PCで7zのファイルをzipに圧縮しなおして転送する。  
Lemuroid用に実装した。

###### (ゲーム数)
randモードの場合に、転送するゲーム数を指定する。

###### (サイズ)
randモードの場合に、転送するファイルの総サイズ数を指定する。  
数字とk, m, gで指定する。

###### (フィルタ文字列)
任意の文字列だが、他のオプションと干渉する場合はダブルクオーテーションで囲んで指定する。

## 免責

開発者は、ソフトウェアの使用に関連するいかなる損害についても一切の責任を負いません。  

