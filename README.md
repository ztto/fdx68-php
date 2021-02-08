# FDX68 for php

FDX68 for php とはFDX68をブラウザベースで使えるようにしたものです。<br>
またFDX68とは GIMONSさん http://retropc.net/gimons/fdx68/ が開発したRaspberry PIを使った<br>
X68000のフロッピーディスクドライブの環境を改善するために開発されたハードウェアとソフトウェアの総称です。<br>

# Setup
* 下記2つの設定を行うことでFDX68 for phpを使うことができます。<br>
 Raspberry PI に対しての初期設定(setup-raspberrypi.txt)<br>
 FDX68 for phpの設定(setup-fdx68.txt)<br>

* それらをまとめて実行する自動シェルとその説明も用意しています。<br>
 Raspberry PI に対しての初期設定(raspberrypi_setup.txt/raspberrypi_setup.sh)<br>
 FDX68 for phpの設定(fdx68php_setup.txt/fdx68php_setup.sh)<br>

# Raspberry PI に対しての初期設定(raspberrypi_setup.txt/raspberrypi_setup.sh)<br>
1.Raspberry PIのインストール<br>
・Raspberry PIにRaspberry PI OSをインストールします<br>
  インストールといってもRaspberry PI OS Liteを下記ツールでSD CARDに書き込むだけです<br>
  イメージは下記urlのダウンロードから<br>
  https://www.raspberrypi.org/<br>
・Win32DiskImager でイメージの書き込み<br>
  Raspberry PI OS Liteをsdcardに書き込みます<br>
  https://ja.osdn.net/projects/sfnet_win32diskimager/<br>


2.SSHを有効にする<br>
・bootパーティションに[ssh]という名前のファイルを作成<br>
 ※拡張子なしで作成してください。これでwindowsの各種ターミナルソフトから<br>
   Raspberry PIへログインすることができます<br>

3.Wi-Fiの設定をする<br>
・bootパーティションに「wpa_supplicant.conf」を作成<br>
 ※Wi-Fiを使用する方はこのファイルを作成することで<br>
   Raspberry PIをWi-Fiに参加することができます<br>
----------ファイルに記述----------<br>
country=JP<br>
ctrl_interface=/var/run/wpa_supplicant<br>
network={<br>
    ssid="SSID"<br>
    psk="パスワード"<br>
    key_mgmt=WPA-PSK<br>
    proto=WPA WPA2<br>
    pairwise=CCMP TKIP<br>
    group=CCMP TKIP WEP104 WEP40<br>
}<br>
----------ファイルに記述----------<br>

4.起動<br>
・Raspberry PIを起動します。<br>
 ※上記までの準備が出来たらRaspberry PIを起動します。<br>
   idおよびパスワードは初期は以下となっています<br>
id:pi<br>
password:raspberry<br>

5.Raspberry PIの初期設定<br>
・gitからセットアップファイルを落として実行します<br>
 ※以下のコマンドをコピー＆ペーストするだけでRaspberry PIの初期設定が完了します。<br>
wget https://raw.githubusercontent.com/ztto/rascsi-php/master/raspberrypi_setup.sh<br>
chmod 755 raspberrypi_setup.sh<br>
sudo ./raspberrypi_setup.sh<br>


# FDX68 for phpの設定(fdx68php_setup.txt/fdx68php_setup.sh)<br>
1.FDX68 for phpの設定<br>
・gitからセットアップファイルを落として実行します<br>
 ※以下のコマンドをコピー＆ペーストするだけでFDX68 for phpの設定が完了します。<br>
wget https://raw.githubusercontent.com/ztto/fdx68-php/master/fdx68php_setup.sh<br>
chmod 755 fdx68php_setup.sh<br>
sudo ./fdx68php_setup.sh<br>
