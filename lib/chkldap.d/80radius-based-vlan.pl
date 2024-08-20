package chkldap::radius_based_vlan;

use strict;
use warnings;
use IServ::Conf;
use IServ::DB;
use Net::LDAP::Util qw(escape_filter_value escape_dn_value);

my $fallback_vlan_id = $conf->{RadiusBasedVlanFallbackId} // undef;

# A VLAN ID must be between 0 (really 1 - 0 indicates disabled) and 4096
unless ($fallback_vlan_id =~ /^[0-9]+$/ and
    $fallback_vlan_id ge 0 and
    $fallback_vlan_id le 4095)
{
  warn "Invalid value for RadiusBasedVlanFallbackId!\n";
  undef $fallback_vlan_id;
}

# Disable fallback setting
undef $fallback_vlan_id if defined $fallback_vlan_id and
    $fallback_vlan_id eq 0;

my (%hosts, %users);

# Use matching SQL query depending on if we're using a fallback VLAN ID or not
if (defined $fallback_vlan_id)
{
  %users = IServ::DB::SelectAll_Hash <<SQL
SELECT DISTINCT ON (actuser) actuser, vlan_id, priority FROM (
  SELECT
    m.actuser,
    r1.vlan_id,
    r1.priority
  FROM radius_vlan r1
    INNER JOIN radius_vlan_group vg ON r1.id = vg.vlan_id
    INNER JOIN members m ON vg.grp = m.actgrp
  UNION
  SELECT ur.act AS actuser, r2.vlan_id, r2.priority FROM radius_vlan r2
    INNER JOIN radius_vlan_role vr ON r2.id = vr.vlan_id
    INNER JOIN user_roles ur ON ur.role = vr.role
  UNION
  SELECT
    u.act AS actuser,
    ? AS vlan_id,
    (SELECT MAX(r3.priority) FROM radius_vlan r3) + 1 AS priority
  FROM users u ORDER BY priority ASC
) AS q ORDER BY q.actuser, q.priority
SQL
  , $fallback_vlan_id;
  %hosts = IServ::DB::SelectAll_Hash <<SQL
SELECT
  DISTINCT ON (q.name) q.name,
  description,
  inv_number,
  ip,
  mac,
  owner,
  q.uuid,
  vlan_id,
  priority,
  r.name AS room
FROM (
  SELECT
    h.description,
    h.inv_number,
    h.ip,
    h.mac,
    h.name,
    h.owner,
    h.room_id,
    h.uuid,
    r1.vlan_id,
    r1.priority
  FROM radius_vlan r1
    RIGHT JOIN hosts h ON h.ip << r1.ip_range
  WHERE h.mac IS NOT NULL
  UNION
  SELECT
    h2.description,
    h2.inv_number,
    h2.ip,
    h2.mac,
    h2.name,
    h2.owner,
    h2.room_id,
    h2.uuid,
    ? AS vlan_id,
    (SELECT MAX(r3.priority) FROM radius_vlan r3) + 1 AS priority
  FROM hosts h2
) 
AS q
LEFT JOIN rooms r ON q.room_id = r.id
ORDER BY q.name, q.priority
SQL
  , $fallback_vlan_id;
}
else
{
  %users = IServ::DB::SelectAll_Hash <<SQL
SELECT DISTINCT ON (actuser) actuser, vlan_id, priority FROM (
  SELECT
    m.actuser,
    r1.vlan_id,
    r1.priority
  FROM radius_vlan r1
    INNER JOIN radius_vlan_group vg ON r1.id = vg.vlan_id
    INNER JOIN members m ON vg.grp = m.actgrp
  UNION
  SELECT ur.act AS actuser, r2.vlan_id, r2.priority FROM radius_vlan r2
    INNER JOIN radius_vlan_role vr ON r2.id = vr.vlan_id
    INNER JOIN user_roles ur ON ur.role = vr.role
) AS q ORDER BY q.actuser, q.priority
SQL
  ;
  %hosts = IServ::DB::SelectAll_Hash <<SQL
SELECT
  DISTINCT ON (q.name) q.name,
  description,
  inv_number,
  ip,
  mac,
  owner,
  q.uuid,
  vlan_id,
  priority,
  r.name AS room
FROM (
  SELECT
    h.description,
    h.inv_number,
    h.ip,
    h.mac,
    h.name,
    h.owner,
    h.room_id,
    h.uuid,
    r1.vlan_id,
    r1.priority
  FROM radius_vlan r1
    RIGHT JOIN hosts h ON h.ip << r1.ip_range
) AS q
LEFT JOIN rooms r ON q.room_id = r.id
ORDER BY q.name, q.priority
SQL
  ;
}

my %rooms = IServ::DB::SelectAll_Hash "SELECT name, room_no, uuid FROM rooms";

# Add radiusProfile to all IServ users which have a VLAN ID assigned. If there
# is not explicit VLAN set and we do not have a fallback, "unknown" users will
# not get the object class "radiusprofile".
for my $act (sort keys %users)
{
  ::want ::dn(cn => escape_dn_value($act), ou => "users"),
    objectClass => [
      "radiusprofile"
    ],
    radiusTunnelMediumType => "IEEE-802",
    radiusTunnelPrivateGroupId => $users{$act}{vlan_id},
    radiusTunnelType => "VLAN",
  ;
}

::want ::dn(ou => "rooms"),
  objectClass => [ "organizationalUnit" ],
  ou => "rooms"
;

# Write as much room information from database as possible to LDAP:
# * name
# * location
# * uuid
for my $name (sort keys %rooms)
{
  ::want ::dn(cn => escape_dn_value($name), ou => "rooms"),
    cn => escape_dn_value($name),
    objectClass => [
      "room",
      "uuidObject"
    ],
    roomNumber => $rooms{$name}{room_no},
    uuid => $rooms{$name}{uuid}
  ;
}

::want ::dn(ou => "hosts"),
  objectClass => [ "organizationalUnit" ],
  ou => "hosts"
;

# Write as much host information from database as possible to LDAP:
# * name
# * description
# * IP address
# * MAC address
# * Serial number (inventory number)
# * owner (DN of IServ user)
# * uuid
# * add radiusProfile to all hosts which have a VLAN ID
for my $name (sort keys %hosts)
{
  ::want ::dn(cn => escape_dn_value($name), ou => "hosts"),
    cn => escape_dn_value($name),
    objectClass => [
      "device",
      "ieee802Device",
      "ipHost",
      "memberOfGroup",
      "uuidObject"
    ],
    description => $hosts{$name}{description},
    ipHostNumber => $hosts{$name}{ip},
    macAddress => $hosts{$name}{mac},
    owner => length $hosts{$name}{owner} ? ::dn(cn => $hosts{$name}{owner}, ou => "users") : undef,
    serialNumber => $hosts{$name}{inv_number},
    uuid => $hosts{$name}{uuid}
  ;

  if (defined $hosts{$name}{vlan_id})
  {
    ::want ::dn(cn => escape_dn_value($name), ou => "hosts"),
      objectClass => [
        "radiusprofile",
      ],
      radiusTunnelMediumType => "IEEE-802",
      radiusTunnelPrivateGroupId => $hosts{$name}{vlan_id},
      radiusTunnelType => "VLAN"
  }

  if (defined $hosts{$name}{room})
  {
    ::want ::dn(cn => escape_dn_value($name), ou => "hosts"),
      memberOf => ::dn(cn => escape_dn_value($hosts{$name}{room}), ou => "rooms"),
    ;
  }
}

1;
