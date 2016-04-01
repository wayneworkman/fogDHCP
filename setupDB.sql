USE fog;
CREATE TABLE IF NOT EXISTS dhcpGlobals(
dgID int NOT NULL AUTO_INCREMENT,
dgOption VARCHAR(255),
PRIMARY KEY (dgID)
);
CREATE TABLE IF NOT EXISTS dhcpSubnets(
dsID int NOT NULL AUTO_INCREMENT,
dsSubnet VARCHAR(50) NOT NULL,
dsNetmask VARCHAR(50) NOT NULL,
dsOptionSubnetMask VARCHAR(50),
dsRangeDynamicBootpStart VARCHAR(50),
dsRangeDynamicBootpEnd VARCHAR(50),
dsDefaultLeaseTime int,
dsMaxLeaseTime int,
dsOptionRouters VARCHAR(50),
dsOptionDomainNameServers VARCHAR(255),
dsOptionNtpServers VARCHAR(255),
dsNextServer VARCHAR(50),
dsCustomArea1 VARCHAR(255),
dsCustomArea2 VARCHAR(255),
dsCustomArea3 VARCHAR(255),
PRIMARY KEY (dsID)
);
CREATE TABLE IF NOT EXISTS dhcpClasses(
dcID int NOT NULL AUTO_INCREMENT,
dc_dsID int NOT NULL,
dcClass VARCHAR(255) NOT NULL,
dcMatch VARCHAR(255) NOT NULL,
dcMatchOption1 VARCHAR(255) NOT NULL,
dcMatchOption2 VARCHAR(255),
dcMatchOption3 VARCHAR(255),
PRIMARY KEY (dcID)
);
CREATE TABLE IF NOT EXISTS dhcpFilenames(
dfID int NOT NULL AUTO_INCREMENT,
dfFileName VARCHAR(255) NOT NULL UNIQUE,
dfDescription VARCHAR(255),
PRIMARY KEY (dfID)
);
CREATE TABLE IF NOT EXISTS dhcpReservations(
drID int NOT NULL AUTO_INCREMENT,
dr_dsID int NOT NULL,
drMAC VARCHAR(17) NOT NULL,
drName VARCHAR(16) NOT NULL,
drFilename VARCHAR(255),
drIP VARCHAR(50) UNIQUE,
drOptionDomainNameServers VARCHAR(255),
drCustomArea1 VARCHAR(255),
drCustomArea2 VARCHAR(255),
drCustomArea3 VARCHAR(255),
PRIMARY KEY (drID)
);


