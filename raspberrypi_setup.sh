#!/usr/bin/env bash

#
#
function conform_check() {

	# sudo 
	if [[ "$(id -u)" -ne 0 ]]; then
	 	echo "Try 'sudo $0'"
	        echo "  実行する場合は 'sudo $0' と入力して下さい."
		exit 1
	fi
}

function board_check() {

	echo "----------------------------"
	echo "  対象となるボードを選んでください."
	echo "  1:RaSCSI"
	echo "  2:FDX68"
	echo "  q:終了"
	read input

	if [ -z $input ] ; then
		board_check

	elif [ $input = '1' ] ; then
		board='RaSCSI'

	elif [ $input = '2' ] ; then
		board='FDX68'

	elif [ $input = 'q' ] || [ $input = 'Q' ] ; then
		echo "  スクリプトを終了します."
		exit 1

	else
		board_check

	fi
}

function rascsi_check() {

	echo "----------------------------"
	echo "  対象となるボードを選んでください."
	echo "  1:standard版"
	echo "  2:fullspec版"
	echo "  3:aibom版"
	echo "  4:gamernium版"
	echo "  q:終了"
	read input

	if [ -z $input ] ; then
		rascsi_check

	elif [ $input = '1' ] ; then
		bpath='standard'

	elif [ $input = '2' ] ; then
		bpath='fullspec'

	elif [ $input = '3' ] ; then
		bpath='aibom'

	elif [ $input = '4' ] ; then
		bpath='gamernium'

	elif [ $input = 'q' ] || [ $input = 'Q' ] ; then
		echo "  スクリプトを終了します."
		exit 1

	else
		rascsi_check

	fi
}

# update package and firmware
function update_package(){

	# update package
	apt-get -y update
	apt-get -y upgrade
	apt-get dist-upgrade

	# update firmware
	#rpi-update
}

function update_raspiconf(){

	# Expand Filesystem
	raspi-config nonint do_expand_rootfs

	# Change Timezone
	raspi-config nonint do_change_timezone Asia/Tokyo

	# Change Locale
	locale-gen en_US.UTF-8
	locale-gen ja_JP.EUC-JP
	locale-gen ja_JP.UTF-8
	raspi-config nonint do_change_locale en_US.UTF-8 UTF-8
}

# install apt-get package
function apt_get_install(){

	# Install Samba
	if ! grep "raspberry pi" "/etc/samba/smb.conf" >/dev/null; then

		apt-get install  -y samba
	   	cp -p /etc/samba/smb.conf /etc/samba/smb.conf.org
		echo "[public]" >> /etc/samba/smb.conf
		echo "   comment = raspberry pi" >> /etc/samba/smb.conf
		echo "   path = /home/pi" >> /etc/samba/smb.conf
		echo "   public = yes" >> /etc/samba/smb.conf
		echo "   read only = no" >> /etc/samba/smb.conf
		echo "   browsable = yes" >> /etc/samba/smb.conf
		echo "   guest ok = yes" >> /etc/samba/smb.conf
		echo "   force user = pi" >> /etc/samba/smb.conf
		echo "   force create mode = 0777" >> /etc/samba/smb.conf
		echo "   force directory mode = 0777" >> /etc/samba/smb.conf
        fi
}
	
# install rascsi package
function rascsi_install(){

	# Install Rascsi
	cd /tmp
	wget http://retropc.net/gimons/rascsi/rascsi141.zip
	unzip rascsi141.zip
	cd rascsi141/bin/raspberrypi/
	tar xzvf rascsi.tar.gz
	cd ${bpath}
	cp -p * /usr/local/bin
	cd /tmp
	rm -r rascsi141.zip rascsi141

	mkdir /home/pi/rasimg
	chmod 777 /home/pi/rasimg

	echo "!/bin/sh" > /home/pi/rasimg/rasmount.sh
	echo "rascsi -ID0 /home/pi/rasimg/scsiimg0.hds -ID6 bridge" >> /home/pi/rasimg/rasmount.sh
	chmod 755 /home/pi/rasimg/rasmount.sh

	echo "[Unit]" > /etc/systemd/system/rascsi.service
	echo "Description=RaSCSI_Service" >> /etc/systemd/system/rascsi.service
	echo "After=syslog.target" >> /etc/systemd/system/rascsi.service
	echo "[Service]" >> /etc/systemd/system/rascsi.service
	echo "Type=simple" >> /etc/systemd/system/rascsi.service
	echo "ExecStart=/usr/bin/sudo /home/pi/rasimg/rasmount.sh" >> /etc/systemd/system/rascsi.service
	echo "TimeoutStopSec=5" >> /etc/systemd/system/rascsi.service
	echo "StandardOutput=null" >> /etc/systemd/system/rascsi.service
	echo "Restart=no" >> /etc/systemd/system/rascsi.service
	echo "[Install]" >> /etc/systemd/system/rascsi.service
	echo "WantedBy = multi-user.target" >> /etc/systemd/system/rascsi.service

	dd if=/dev/zero of=/home/pi/rasimg/scsiimg0.hds bs=1M count=40

	systemctl enable rascsi
}

# install fdx68 package
function fdx68_install(){

	# Install fdx68
	mkdir /tmp/fdx68
	cd /tmp/fdx68
	wget http://retropc.net/gimons/fdx68/fdx68_114.tar.gz
	tar xzvf fdx68_114.tar.gz
        rm -r fdx68_114.tar.gz
        cp -p * /usr/local/bin

	mkdir /home/pi/fdximg
	chmod 777 /home/pi/fdximg
}

conform_check
board_check
if [ $board = 'RaSCSI' ] ; then
	rascsi_check
fi
update_package
update_raspiconf
apt_get_install
if [ $board = 'RaSCSI' ] ; then
	rascsi_install
else
	fdx68_install
fi

echo "Please sudo reboot"
exit 0
