<?php
// File would be for users to configure their access permissions.

require_once 'Acl.php';
$acl = Acl::getInstance();

// 'access' to a cluster means:
//  - viewing the cluster even if it's private
//  - editing cluster config, like views.
// Currently no way to have a private cluster which can be viewed but not edited.

// By default, a cluster can be viewed but not edited.

// admin can access & edit all clusters.
$acl->addUser('admin', 'a94a8fe5ccb19ba61c4c0873d391e987982fbbd3', '*');

// some user can access cluster1
$acl->addUser('someuser', 'a94a8fe5ccb19ba61c4c0873d391e987982fbbd3', 'cluster1');

// other user can access cluster1 and cluster2
$acl->addUser('otheruser', 'a94a8fe5ccb19ba61c4c0873d391e987982fbbd3', 'cluster1', 'cluster2');

// cluster2 is private.  User must be explicitly allowed to view it.
$acl->setClusterPrivate('cluster2');
?>
