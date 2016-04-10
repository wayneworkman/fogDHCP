configureDHCP() {
    dots "Setting up and starting DHCP Server"
    case $bldhcp in
        1)
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
            defaultLeaseTime=21600
            maxLeaseTime=43200
  


            
#-----Begin Temporary Lines-----#
mysql -s -D fog -e "DROP TABLE dhcpGlobals"
mysql -s -D fog -e "DROP TABLE dhcpSubnets"
mysql -s -D fog -e "DROP TABLE dhcpClasses"
mysql -s -D fog -e "DROP TABLE dhcpFilenames"
mysql -s -D fog -e "DROP TABLE dhcpReservations"
mysql -s -D fog -e "DELETE FROM globalSettings WHERE settingKey = 'DHCP_RESTART_COMMAND'"
mysql -s -D fog -e "DELETE FROM globalSettings WHERE settingKey = 'DHCP_STATUS_COMMAND'"
mysql -s -D fog -e "DELETE FROM globalSettings WHERE settingKey = 'DHCP_TO_USE'"
mysql -s -D fog -e "DELETE FROM globalSettings WHERE settingKey = 'DHCP_SERVICE_SLEEP_TIME'"
mysql -s -D fog -e "DELETE FROM globalSettings WHERE settingKey = 'DHCP_METHOD'"
mysql -s -D fog -e "DELETE FROM globalSettings WHERE settingKey = 'ONLY_LOG_CHANGES'"
mysql -s -D fog -e "DELETE FROM globalSettings WHERE settingKey = 'DHCP_SERVICE_ENABLED'"
#-----End Temporary Lines-----#

            mysql < /var/www/html/fogDHCP/setupDB.sql
            dhcpDataExists=$(mysql -s -D fog -e "SELECT COUNT(*) FROM globalSettings where settingKey = 'DHCP_Service_Sleep_Time' ")

            [[ $dhcpDataExists == 0 ]] && mysql -s -D fog -e "INSERT INTO globalSettings (settingKey,settingDesc,settingValue,settingCategory) VALUES ('DHCP_SERVICE_ENABLED','This setting controls if the DHCP manager service should attempt to do anything or not. 1 means yes, 0 means no.','1','DHCP');"

            [[ $dhcpDataExists == 0 ]] && mysql -s -D fog -e "INSERT INTO globalSettings (settingKey,settingDesc,settingValue,settingCategory) VALUES ('DHCP_SERVICE_SLEEP_TIME','This setting controls how often in seconds the DHCP service will check for changes made to DHCP. If changes are found, the dhcp configuration file is updated and a DHCP service restart is attempted.','60','FOG Linux Service Sleep Times');"

            [[ $dhcpDataExists == 0 ]] && mysql -s -D fog -e "INSERT INTO globalSettings (settingKey,settingDesc,settingValue,settingCategory) VALUES ('DHCP_TO_USE','This specifies the path of the DHCP configuration file on this system. This file is the target of the DHCP updating mechanism.','$dhcptouse','DHCP')"

            [[ $dhcpDataExists == 0 ]] && mysql -s -D fog -e "INSERT INTO globalSettings (settingKey,settingDesc,settingValue,settingCategory) VALUES ('ONLY_LOG_CHANGES','This setting is to prevent the fog dhcp service log from becoming very large. When this is enabled (1), only changes and errors are logged. If this is disabled, a log message saying everything is fine is written with every service iteration.','1','DHCP')"

            dgOption="# DHCP Server Configuration file"
            [[ $dhcpDataExists == 0 ]] && mysql -s -D fog -e "INSERT INTO dhcpGlobals (dgOption) VALUES ('$dgOption')"
            echo $dgOption > $dhcptouse
            dgOption="#see /usr/share/doc/dhcp*/dhcpd.conf.sample"
            [[ $dhcpDataExists == 0 ]] && mysql -s -D fog -e "INSERT INTO dhcpGlobals (dgOption) VALUES ('$dgOption')"
            echo $dgOption > $dhcptouse
            dgOption="# This file was created by FOG"
            [[ $dhcpDataExists == 0 ]] && mysql -s -D fog -e "INSERT INTO dhcpGlobals (dgOption) VALUES ('$dgOption')"
            echo $dgOption > $dhcptouse
            dgOption="#Definition of PXE-specific options"
            [[ $dhcpDataExists == 0 ]] && mysql -s -D fog -e "INSERT INTO dhcpGlobals (dgOption) VALUES ('$dgOption')"
            echo $dgOption > $dhcptouse
            dgOption="# Code 1: Multicast IP Address of bootfile"
            [[ $dhcpDataExists == 0 ]] &&mysql -s -D fog -e "INSERT INTO dhcpGlobals (dgOption) VALUES ('$dgOption')"
            echo $dgOption > $dhcptouse
            dgOption="# Code 2: UDP Port that client should monitor for MTFTP Responses"
            [[ $dhcpDataExists == 0 ]] && mysql -s -D fog -e "INSERT INTO dhcpGlobals (dgOption) VALUES ('$dgOption')"
            echo $dgOption > $dhcptouse
            dgOption="# Code 3: UDP Port that MTFTP servers are using to listen for MTFTP requests"
            [[ $dhcpDataExists == 0 ]] && mysql -s -D fog -e "INSERT INTO dhcpGlobals (dgOption) VALUES ('$dgOption')"
            echo $dgOption > $dhcptouse
            dgOption="# Code 4: Number of seconds a client must listen for activity before trying"
            [[ $dhcpDataExists == 0 ]] && mysql -s -D fog -e "INSERT INTO dhcpGlobals (dgOption) VALUES ('$dgOption')"
            echo $dgOption > $dhcptouse
            dgOption="#         to start a new MTFTP transfer"
            [[ $dhcpDataExists == 0 ]] && mysql -s -D fog -e "INSERT INTO dhcpGlobals (dgOption) VALUES ('$dgOption')"
            echo $dgOption > $dhcptouse
            dgOption="# Code 5: Number of seconds a client must listen before trying to restart"
            [[ $dhcpDataExists == 0 ]] && mysql -s -D fog -e "INSERT INTO dhcpGlobals (dgOption) VALUES ('$dgOption')"
            echo $dgOption > $dhcptouse
            dgOption="#         a MTFTP transfer"
            [[ $dhcpDataExists == 0 ]] && mysql -s -D fog -e "INSERT INTO dhcpGlobals (dgOption) VALUES ('$dgOption')"
            echo $dgOption > $dhcptouse
            dgOption="option space PXE;"
            [[ $dhcpDataExists == 0 ]] && mysql -s -D fog -e "INSERT INTO dhcpGlobals (dgOption) VALUES ('$dgOption')"
            echo $dgOption > $dhcptouse
            dgOption="option PXE.mtftp-ip code 1 = ip-address\;"
            [[ $dhcpDataExists == 0 ]] && mysql -s -D fog -e "INSERT INTO dhcpGlobals (dgOption) VALUES ('$dgOption')"
            echo "option PXE.mtftp-ip code 1 = ip-address;" > $dhcptouse
            dgOption="option PXE.mtftp-cport code 2 = unsigned integer 16\;"
            [[ $dhcpDataExists == 0 ]] && mysql -s -D fog -e "INSERT INTO dhcpGlobals (dgOption) VALUES ('$dgOption')"
            echo "option PXE.mtftp-cport code 2 = unsigned integer 16;" > $dhcptouse
            dgOption="option PXE.mtftp-sport code 3 = unsigned integer 16\;"
            [[ $dhcpDataExists == 0 ]] && mysql -s -D fog -e "INSERT INTO dhcpGlobals (dgOption) VALUES ('$dgOption')"
            echo "option PXE.mtftp-sport code 3 = unsigned integer 16;" > $dhcptouse
            dgOption="option PXE.mtftp-tmout code 4 = unsigned integer 8\;"
            [[ $dhcpDataExists == 0 ]] && mysql -s -D fog -e "INSERT INTO dhcpGlobals (dgOption) VALUES ('$dgOption')"
            echo "option PXE.mtftp-tmout code 4 = unsigned integer 8;" > $dhcptouse
            dgOption="option PXE.mtftp-delay code 5 = unsigned integer 8\;"
            [[ $dhcpDataExists == 0 ]] && mysql -s -D fog -e "INSERT INTO dhcpGlobals (dgOption) VALUES ('$dgOption')"
            echo "option PXE.mtftp-delay code 5 = unsigned integer 8;" > $dhcptouse
            dgOption="option arch code 93 = unsigned integer 16\;"
            [[ $dhcpDataExists == 0 ]] && mysql -s -D fog -e "INSERT INTO dhcpGlobals (dgOption) VALUES ('$dgOption')"
            echo "option arch code 93 = unsigned integer 16;" > $dhcptouse
            dgOption="use-host-decl-names on\;"
            [[ $dhcpDataExists == 0 ]] && mysql -s -D fog -e "INSERT INTO dhcpGlobals (dgOption) VALUES ('$dgOption')"
            echo "use-host-decl-names on;" > $dhcptouse
            dgOption="ddns-update-style interim\;"
            [[ $dhcpDataExists == 0 ]] && mysql -s -D fog -e "INSERT INTO dhcpGlobals (dgOption) VALUES ('$dgOption')"
            echo "ddns-update-style interim;" > $dhcptouse
            dgOption="ignore client-updates\;"
            [[ $dhcpDataExists == 0 ]] && mysql -s -D fog -e "INSERT INTO dhcpGlobals (dgOption) VALUES ('$dgOption')"
            echo "ignore client-updates;" > $dhcptouse
            

            echo "subnet $network netmask $submask{" >> "$dhcptouse"
            echo "    option subnet-mask $submask;" >> "$dhcptouse"
            echo "    range dynamic-bootp $startrange $endrange;" >> "$dhcptouse"
            echo "    default-lease-time $defaultLeaseTime;" >> "$dhcptouse"
            echo "    max-lease-time $maxLeaseTime;" >> "$dhcptouse"
            [[ ! $(validip $routeraddress) -eq 0 ]] && routeraddress=$(echo $routeraddress | grep -oE "\b([0-9]{1,3}\.){3}[0-9]{1,3}\b")
            [[ ! $(validip $dnsaddress) -eq 0 ]] && dnsaddress=$(echo $dnsaddress | grep -oE "\b([0-9]{1,3}\.){3}[0-9]{1,3}\b")
            [[ $(validip $routeraddress) -eq 0 ]] && echo "    option routers $routeraddress;" >> "$dhcptouse" || ( echo "    #option routers 0.0.0.0" >> "$dhcptouse" && echo " !!! No router address found !!!" )
            [[ $(validip $dnsaddress) -eq 0 ]] && echo "    option domain-name-servers $dnsaddress;" >> "$dhcptouse" || ( echo "    #option routers 0.0.0.0" >> "$dhcptouse" && echo " !!! No dns address found !!!" )
            
            echo "    next-server $ipaddress;" >> "$dhcptouse"

            if [[ $dhcpDataExists == 0 ]]; then

                mysql -s -D fog -e "INSERT INTO dhcpSubnets (dsSubnet,dsNetmask,dsOptionSubnetMask,dsRangeDynamicBootpStart,dsRangeDynamicBootpEnd,dsDefaultLeaseTime,dsMaxLeaseTime,dsOptionRouters,dsOptionDomainNameServers,dsNextServer) VALUES ('$network','$submask','$submask','$startrange','$endrange','$defaultLeaseTime','$maxLeaseTime','$routeraddress','$dnsaddress','$ipaddress')"  

                dsID=$(mysql -s -D fog -e "SELECT dsID FROM dhcpSubnets WHERE dsSubnet = '$network'")
                dcClass="Legacy"
                dcMatch="match if substring(option vendor-class-identifier, 0, 20) = \"PXEClient:Arch:00000\"\;"
                dcMatchOption="filename \"undionly.kkpxe\"\;"
                mysql -s -D fog -e "INSERT INTO dhcpClasses (dc_dsID,dcClass,dcMatch,dcMatchOption1) VALUES ('$dsID','$dcClass','$dcMatch','$dcMatchOption')"
                dcClass="UEFI-32-2"
                dcMatch="match if substring(option vendor-class-identifier, 0, 20) = \"PXEClient:Arch:00002\"\;"
                dcMatchOption="filename \"i386-efi/ipxe.efi\"\;"
                mysql -s -D fog -e "INSERT INTO dhcpClasses (dc_dsID,dcClass,dcMatch,dcMatchOption1) VALUES ('$dsID','$dcClass','$dcMatch','$dcMatchOption')"
                dcClass="UEFI-32-1"
                dcMatch="match if substring(option vendor-class-identifier, 0, 20) = \"PXEClient:Arch:00006\"\;"
                dcMatchOption="filename \"i386-efi/ipxe.efi\"\;"
                mysql -s -D fog -e "INSERT INTO dhcpClasses (dc_dsID,dcClass,dcMatch,dcMatchOption1) VALUES ('$dsID','$dcClass','$dcMatch','$dcMatchOption')"
                dcClass="UEFI-64-1"
                dcMatch="match if substring(option vendor-class-identifier, 0, 20) = \"PXEClient:Arch:00007\"\;"
                dcMatchOption="filename \"ipxe.efi\"\;"
                mysql -s -D fog -e "INSERT INTO dhcpClasses (dc_dsID,dcClass,dcMatch,dcMatchOption1) VALUES ('$dsID','$dcClass','$dcMatch','$dcMatchOption')"
                dcClass="UEFI-64-2"
                dcMatch="match if substring(option vendor-class-identifier, 0, 20) = \"PXEClient:Arch:00008\"\;"
                dcMatchOption="filename \"ipxe.efi\"\;"
                mysql -s -D fog -e "INSERT INTO dhcpClasses (dc_dsID,dcClass,dcMatch,dcMatchOption1) VALUES ('$dsID','$dcClass','$dcMatch','$dcMatchOption')"
                dcClass="UEFI-64-3"
                dcMatch="match if substring(option vendor-class-identifier, 0, 20) = \"PXEClient:Arch:00009\"\;"
                dcMatchOption="filename \"ipxe.efi\"\;"
                mysql -s -D fog -e "INSERT INTO dhcpClasses (dc_dsID,dcClass,dcMatch,dcMatchOption1) VALUES ('$dsID','$dcClass','$dcMatch','$dcMatchOption')"
            fi
            
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
                    #Set systemctl type commands as 3.
                    [[ $dhcpDataExists == 0 ]] && mysql -s -D fog -e "INSERT INTO globalSettings (settingKey,settingDesc,settingValue,settingCategory) VALUES ('DHCP_METHOD','This determines what commands are issued for restarting DHCP and checking the service. Options are 0 for nothing, 1, 2, and 3. The only reason for changing this value is if the FOG DB is imported into a different OS.','3','DHCP')"
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
                            #Case 1 is set as 1.
                            [[ $dhcpDataExists == 0 ]] && mysql -s -D fog -e "INSERT INTO globalSettings (settingKey,settingDesc,settingValue,settingCategory) VALUES ('DHCP_METHOD','This determines what commands are issued for restarting DHCP and checking the service. Options are 0 for nothing, 1, 2, and 3. The only reason for changing this value is if the FOG DB is imported into a different OS.','1','DHCP')"
                            chkconfig $dhcpd on >>$workingdir/error_logs/fog_error_${version}.log 2>&1
                            service $dhcpd stop >>$workingdir/error_logs/fog_error_${version}.log 2>&1
                            sleep 2
                            service $dhcpd start >>$workingdir/error_logs/fog_error_${version}.log 2>&1
                            sleep 2
                            service $dhcpd status >>$workingdir/error_logs/fog_error_${version}.log 2>&1
                            ;;
                        2)
                            #Case 2 is set as 2.
                            [[ $dhcpDataExists == 0 ]] && mysql -s -D fog -e "INSERT INTO globalSettings (settingKey,settingDesc,settingValue,settingCategory) VALUES ('DHCP_METHOD','This determines what commands are issued for restarting DHCP and checking the service. Options are 0 for nothing, 1, 2, and 3. The only reason for changing this value is if the FOG DB is imported into a different OS.','2','DHCP')"
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
            [[ $dhcpDataExists == 0 ]] && mysql -s -D fog -e "INSERT INTO globalSettings (settingKey,settingDesc,settingValue,settingCategory) VALUES ('DHCP_METHOD','This determines what commands are issued for restarting DHCP and checking the service. Options are 0 for nothing, 1, 2, and 3. The only reason for changing this value is if the FOG DB is imported into a different OS.','0','DHCP')"
            echo "Skipped"
            ;;
    esac
}
