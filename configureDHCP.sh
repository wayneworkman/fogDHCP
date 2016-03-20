configureDHCP() {
    dots "Setting up and starting DHCP Server"
    case $bldhcp in
        1)
            mysql -s -D fog -e "INSERT INTO fileChecksums (fileHost,fileTime,fileSum,fileLocation) VALUES ('$ipaddress','$fileTime','$fileSum','$fileLocation')"




            [[ -f $dhcpconfig ]] && cp -f $dhcpconfig ${dhcpconfig}.fogbackup
            serverip=$(ip -4 -o addr show $interface | awk -F'([ /])+' '/global/ {print $4}')
            [[ -z $serverip ]] && serverip=$(/sbin/ifconfig $interface | grep -oE 'inet[:]? addr[:]?([0-9]{1,3}\.){3}[0-9]{1,3}' | awk -F'(inet[:]? ?addr[:]?)' '{print $2}')
            [[ -z $submask ]] && submask=$(cidr2mask $(getCidr $interface))
            network=$(mask2network $serverip $submask)
            [[ -z $startrange ]] && startrange=$(addToAddress $network 10)
            [[ -z $endrange ]] && endrange=$(subtract1fromAddress $(echo $(interface2broadcast $interface)))
            [[ -f $dhcpconfig ]] && dhcptouse=$dhcpconfig
            [[ -f $dhcpconfigother ]] && dhcptouse=$dhcpconfigother
            if [[ -z $dhcptouse || ! -f $dhcptouse ]]; then
                echo "Failed"
                echo "Could not find dhcp config file"
                exit 1
            fi
            [[ -z $bootfilename ]] && bootfilename="undionly.kpxe"
            echo "# DHCP Server Configuration file\n#see /usr/share/doc/dhcp*/dhcpd.conf.sample" > $dhcptouse
            echo "# This file was created by FOG" >> "$dhcptouse"
            echo "#Definition of PXE-specific options" >> "$dhcptouse"
            echo "# Code 1: Multicast IP Address of bootfile" >> "$dhcptouse"
            echo "# Code 2: UDP Port that client should monitor for MTFTP Responses" >> "$dhcptouse"
            echo "# Code 3: UDP Port that MTFTP servers are using to listen for MTFTP requests" >> "$dhcptouse"
            echo "# Code 4: Number of seconds a client must listen for activity before trying" >> "$dhcptouse"
            echo "#         to start a new MTFTP transfer" >> "$dhcptouse"
            echo "# Code 5: Number of seconds a client must listen before trying to restart" >> "$dhcptouse"
            echo "#         a MTFTP transfer" >> "$dhcptouse"
            echo "option space PXE;" >> "$dhcptouse"
            echo "option PXE.mtftp-ip code 1 = ip-address;" >> "$dhcptouse"
            echo "option PXE.mtftp-cport code 2 = unsigned integer 16;" >> "$dhcptouse"
            echo "option PXE.mtftp-sport code 3 = unsigned integer 16;" >> "$dhcptouse"
            echo "option PXE.mtftp-tmout code 4 = unsigned integer 8;" >> "$dhcptouse"
            echo "option PXE.mtftp-delay code 5 = unsigned integer 8;" >> "$dhcptouse"
            echo "option arch code 93 = unsigned integer 16;" >> "$dhcptouse"
            echo "use-host-decl-names on;" >> "$dhcptouse"
            echo "ddns-update-style interim;" >> "$dhcptouse"
            echo "ignore client-updates;" >> "$dhcptouse"
            echo "next-server $ipaddress;" >> "$dhcptouse"
            echo "# Specify subnet of ether device you do NOT want service." >> "$dhcptouse"
            echo "# For systems with two or more ethernet devices." >> "$dhcptouse"
            echo "# subnet 136.165.0.0 netmask 255.255.0.0 {}" >> "$dhcptouse"
            echo "subnet $network netmask $submask{" >> "$dhcptouse"
            echo "    option subnet-mask $submask;" >> "$dhcptouse"
            echo "    range dynamic-bootp $startrange $endrange;" >> "$dhcptouse"
            echo "    default-lease-time 21600;" >> "$dhcptouse"
            echo "    max-lease-time 43200;" >> "$dhcptouse"
            [[ ! $(validip $routeraddress) -eq 0 ]] && routeraddress=$(echo $routeraddress | grep -oE "\b([0-9]{1,3}\.){3}[0-9]{1,3}\b")
            [[ ! $(validip $dnsaddress) -eq 0 ]] && dnsaddress=$(echo $dnsaddress | grep -oE "\b([0-9]{1,3}\.){3}[0-9]{1,3}\b")
            [[ $(validip $routeraddress) -eq 0 ]] && echo "    option routers $routeraddress;" >> "$dhcptouse" || ( echo "    #option routers 0.0.0.0" >> "$dhcptouse" && echo " !!! No router address found !!!" )
            [[ $(validip $dnsaddress) -eq 0 ]] && echo "    option domain-name-servers $dnsaddress;" >> "$dhcptouse" || ( echo "    #option routers 0.0.0.0" >> "$dhcptouse" && echo " !!! No dns address found !!!" )
            echo "    class \"Legacy\" {" >> "$dhcptouse"
            echo "        match if substring(option vendor-class-identifier, 0, 20) = \"PXEClient:Arch:00000\";" >> "$dhcptouse"
            echo "        filename \"undionly.kkpxe\";" >> "$dhcptouse"
            echo "    }" >> "$dhcptouse"
            echo "    class \"UEFI-32-2\" {" >> "$dhcptouse"
            echo "        match if substring(option vendor-class-identifier, 0, 20) = \"PXEClient:Arch:00002\";" >> "$dhcptouse"
            echo "        filename \"i386-efi/ipxe.efi\";" >> "$dhcptouse"
            echo "    }" >> "$dhcptouse"
            echo "    class \"UEFI-32-1\" {" >> "$dhcptouse"
            echo "        match if substring(option vendor-class-identifier, 0, 20) = \"PXEClient:Arch:00006\";" >> "$dhcptouse"
            echo "        filename \"i386-efi/ipxe.efi\";" >> "$dhcptouse"
            echo "    }" >> "$dhcptouse"
            echo "    class \"UEFI-64-1\" {" >> "$dhcptouse"
            echo "        match if substring(option vendor-class-identifier, 0, 20) = \"PXEClient:Arch:00007\";" >> "$dhcptouse"
            echo "        filename \"ipxe.efi\";" >> "$dhcptouse"
            echo "    }" >> "$dhcptouse"
            echo "    class \"UEFI-64-2\" {" >> "$dhcptouse"
            echo "        match if substring(option vendor-class-identifier, 0, 20) = \"PXEClient:Arch:00008\";" >> "$dhcptouse"
            echo "        filename \"ipxe.efi\";" >> "$dhcptouse"
            echo "    }" >> "$dhcptouse"
            echo "    class \"UEFI-64-3\" {" >> "$dhcptouse"
            echo "        match if substring(option vendor-class-identifier, 0, 20) = \"PXEClient:Arch:00009\";" >> "$dhcptouse"
            echo "        filename \"ipxe.efi\";" >> "$dhcptouse"
            echo "    }" >> "$dhcptouse"
            echo "}" >> "$dhcptouse"
            case $systemctl in
                yes)
                    systemctl enable $dhcpd >>$workingdir/error_logs/fog_error_${version}.log 2>&1
                    systemctl stop $dhcpd >>$workingdir/error_logs/fog_error_${version}.log 2>&1
                    sleep 2
                    systemctl start $dhcpd >>$workingdir/error_logs/fog_error_${version}.log 2>&1
                    sleep 2
                    systemctl status $dhcpd >>$workingdir/error_logs/fog_error_${version}.log 2>&1
                    ;;
                *)
                    case $osid in
                        1)
                            chkconfig $dhcpd on >>$workingdir/error_logs/fog_error_${version}.log 2>&1
                            service $dhcpd stop >>$workingdir/error_logs/fog_error_${version}.log 2>&1
                            sleep 2
                            service $dhcpd start >>$workingdir/error_logs/fog_error_${version}.log 2>&1
                            sleep 2
                            service status $dhcpd >>$workingdir/error_logs/fog_error_${version}.log 2>&1
                            ;;
                        2)
                            sysv-rc-conf $dhcpd on >>$workingdir/error_logs/fog_error_${version}.log 2>&1
                            /etc/init.d/$dhcpd stop >>$workingdir/error_logs/fog_error_${version}.log 2>&1
                            sleep 2
                            /etc/init.d/$dhcpd start >>$workingdir/error_logs/fog_error_${version}.log 2>&1 && sleep 2
                            ;;
                    esac
                    ;;
            esac
            errorStat $?
            ;;
        *)
            echo "Skipped"
            ;;
    esac
}
