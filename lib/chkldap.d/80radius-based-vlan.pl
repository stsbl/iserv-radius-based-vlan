package chkldap::radius_based_vlan;

use strict;
use warnings;
use IServ::DB;

my %users = IServ::DB::SelectAll_Hash <<SQL
SELECT DISTINCT ON (actuser) actuser, vlan_id, priority FROM (
  SELECT m.actuser, r1.vlan_id, r1.priority FROM radius_vlan r1
    INNER JOIN radius_vlan_group vg ON r1.id = vg.vlan_id
    INNER JOIN members m ON vg.group = m.actgrp
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
, 123;

for my $act (sort keys %users)
{
  ::want ::dn(cn => $act, ou => "users"),
    objectClass => [
      "radiusprofile"
    ],
    radiusTunnelMediumType => "IEEE-802",
    radiusTunnelPrivateGroupId => $users{$act}{vlan_id},
    radiusTunnelType => "VLAN",
  ;
}

1;
