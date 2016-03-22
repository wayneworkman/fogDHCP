USE fog;
TRUNCATE TABLE dhcpReservations;

INSERT INTO dhcpReservations (drMAC,drName,drIP,drCustomArea1) VALUES ('00:13:72:AB:FD:7C','data','10.0.0.2','#This is the video server.');
INSERT INTO dhcpReservations (drMAC,drName,drIP,drCustomArea1) VALUES ('00:E0:4C:08:08:28','application','10.0.0.7','#This is the old Dimension 8200 Pentium 4.');
INSERT INTO dhcpReservations (drMAC,drName,drIP,drCustomArea1) VALUES ('F0:4D:A2:22:6E:2C','Optiplex380','10.0.0.3','#VM Host');
INSERT INTO dhcpReservations (drMAC,drName,drIP) VALUES ('30:b5:c2:c9:20:9b','TP-Link-Access-point','10.0.0.6');
INSERT INTO dhcpReservations (drMAC,drName,drIP,drCustomArea1) VALUES ('52:54:00:30:28:1e','fog-server','10.0.0.4','#This is a VM.');
INSERT INTO dhcpReservations (drMAC,drName,drIP,drCustomArea1) VALUES ('52:54:00:7b:37:fb','apache','10.0.0.8','#This is a VM.');

