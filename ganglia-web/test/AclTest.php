<?php
$test_dir = dirname(__FILE__);
require $test_dir.'/../lib/Acl.php';

class AclTest extends PHPUnit_Framework_TestCase {
  
  public function setUp() {
    $_SERVER['PHP_AUTH_USER'] = 'alex';
    $_SERVER['PHP_AUTH_PW'] = 'test';
    $this->correct_hash = 'a94a8fe5ccb19ba61c4c0873d391e987982fbbd3';
  }
  
  public function testUserIsAuthorized() {
    $acl = new Acl();
    $acl->addUser('alex', $this->correct_hash, 'cluster');
    $this->assertTrue($acl->authorize('cluster', 'edit'));
  }
  
  public function testWildcardClusterIsSupported() {
    $acl = new Acl();
    $acl->addUser('alex', $this->correct_hash, '*');
    $this->assertTrue($acl->authorize('cluster', 'edit'));
  }
  
  public function testIncorrectPasswordDeniesAccess() {
    $acl = new Acl();
    $acl->addUser('alex', 'stew', '*');
    $this->assertFalse($acl->authorize('cluster', 'edit'));
  }
  
  public function testMultipleClustersMayBeAdded() {
    $acl = new Acl();
    $acl->addUser('alex', $this->correct_hash, 'cluster1', 'cluster2');
    $this->assertTrue($acl->authorize('cluster1', 'edit'));
    $this->assertTrue($acl->authorize('cluster2', 'edit'));
  }
  
  public function testViewIsAllowedByDefault() {
    $acl = new Acl();
    $this->assertTrue($acl->authorize('cluster1', 'view'));
  }
  
  public function testEditIsDeniedByDefault() {
    $acl = new Acl();
    $this->assertFalse($acl->authorize('cluster1', 'edit'));
  }
  
  public function testPrivateClusterCannotBeViewedByUnauthorizedUser() {
    $acl = new Acl();
    $acl->setClusterPrivate('cluster1');
    $this->assertFalse($acl->authorize('cluster1', 'view'));
  }
  
  public function testPrivateClusterCanBeViewedByAuthorizedUser() {
    $acl = new Acl();
    $acl->setClusterPrivate('cluster1');
    $acl->addUser('alex', $this->correct_hash, 'cluster1');
    $this->assertTrue($acl->authorize('cluster1', 'view'));
  }
}
?>