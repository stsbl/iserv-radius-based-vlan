CREATE TABLE radius_vlan (
	ID		SERIAL		PRIMARY KEY,
	Description	TEXT		NOT NULL UNIQUE,
	VLAN_ID		INT		NOT NULL CHECK (VLAN_ID BETWEEN 1 AND 4095),
	Priority	INT		NOT NULL UNIQUE DEFAULT 0,
	Room_ID		INT		REFERENCES rooms(ID)
						ON UPDATE CASCADE
						ON DELETE SET NULL,
	IP_Range	INET
);

CREATE TABLE radius_vlan_group (
	VLAN_ID		INT		NOT NULL REFERENCES radius_vlan(ID)
						ON UPDATE CASCADE
						ON DELETE CASCADE,
	Group		TEXT		REFERENCES groups(Act)
						ON UPDATE CASCADE
						ON DELETE CASCADE,
	Grp		TEXT   		REFERENCES groups(Act)
						ON UPDATE CASCADE
						ON DELETE CASCADE
	--PRIMARY KEY (VLAN_ID, Grp)
);

CREATE INDEX radius_vlan_group_vlan_id_key ON radius_vlan_group(VLAN_ID);

CREATE TABLE radius_vlan_role (
	VLAN_ID		INT		NOT NULL REFERENCES radius_vlan(ID)
						ON UPDATE CASCADE
						ON DELETE CASCADE,
	Role		TEXT		NOT NULL REFERENCES security_roles(Role)
						ON UPDATE CASCADE
						ON DELETE CASCADE,
	PRIMARY KEY (VLAN_ID, Role)
);

CREATE INDEX radius_vlan_role_vlan_id_key ON radius_vlan_role(VLAN_ID);

GRANT SELECT, INSERT, UPDATE, DELETE ON radius_vlan, radius_vlan_group, radius_vlan_role TO symfony;
GRANT SELECT, USAGE ON radius_vlan_id_seq TO symfony;
