package chkldap::radius_based_vlan;
use strict;
use warnings;
use IServ::DB;

for my $act (IServ::DB::SelectCol "SELECT Act FROM users ORDER BY Act")
{
  ::want ::dn(cn => $act, ou => "users"),
    objectClass => [
      "radiusprofile"
    ],
    radiusTunnelMediumType => "IEEE-802",
    radiusTunnelPrivateGroupId => 123,
    radiusTunnelType => "VLAN",
  ;
}

1;
