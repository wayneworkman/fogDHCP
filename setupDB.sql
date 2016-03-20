USE fog;
CREATE TABLE IF NOT EXISTS dhcpSubnets(
dsID int NOT NULL AUTO_INCREMENT,
dsSubnet VARCHAR(50) NOT NULL UNIQUE,
dsNetmask VARCHAR(50) NOT NULL,
dsOptionSubnetMask VARCHAR(50),
dsRangeDynamicBootp VARCHAR(100),
dsDefaultLeaseTime int,
dsMaxLeaseTime int,
dsOptionRouters VARCHAR(50),
dsOptionDomainNameServers VARCHAR(255),
dsNextServer VARCHAR(50),
PRIMARY KEY (dsID)
);
CREATE TABLE IF NOT EXISTS dhcpReservations(
drID int NOT NULL AUTO_INCREMENT,
dr_hmID int NOT NULL,
dr_dsID int NOT NULL,
drFileName VARCHAR(255),
drIP VARCHAR(50) UNIQUE,
PRIMARY KEY (drID)
);
CREATE TABLE IF NOT EXISTS dhcpClasses(
dcID int NOT NULL AUTO_INCREMENT,
dc_dsID int NOT NULL,
dcClass VARCHAR(255),
dcMatch VARCHAR(255),
dcMatchOption VARCHAR(255),
PRIMARY KEY (dcID)
);
