<?php
class Acl {
  private $clusters;
  private $users;
  
  private static $instance;
  
  public static function getInstance() {
    if(!self::$instance) {
      self::$instance = new Acl();
    }
    return self::$instance;
  }
  
  // keeping this public so it's testable.
  public function __construct() {
    $this->clusters = array();
    $this->users = array();
    $this->admins = array();
  }
  
  // addUser('user', 'sha1', '*')
  // addUser('user', 'sha1', 'cluster-name')
  // addUser('user', 'sha1', 'cluster1-name', 'cluster2-name')
  public function addUser($user, $password_hash) {
    $this->users[$user] = $password_hash;
    
    $clusters = array_slice(func_get_args(),2);
    foreach($clusters as $cluster) {
      if($cluster == '*') {
        $this->admins[] = $user;
      } else {
        $this->initCluster($cluster);
        $this->clusters[$cluster]['users'][] = $user;
      }
    }
  }
  
  public function setClusterPrivate($cluster, $value=true) {
    if(!is_bool($value)) {
      throw new Exception("'$value' should be a boolean.");
    }
    $this->initCluster($cluster);
    $this->clusters[$cluster]['private'] = $value;
  }
  
  // action == 'view' || 'edit'
  public function authorize($cluster,$action='view') {
    $this->initCluster($cluster);
    
    $doAuth = false;
    switch($action) {
      case 'view':
        if($this->clusters[$cluster]['private']) {
          $doAuth = true;
        }
        break;
      case 'edit':
        $doAuth = true;
        break;
      default:
        throw new Exception("Unknown action '$action'");
    }
    // a cluster which has not been explicitly configured will allow view & deny edit.
    if($doAuth) {
      $user = $this->getUser();
      return $this->authenticate() && (
        in_array($user, $this->admins) ||
        in_array($user, $this->clusters[$cluster]['users'])
      );
    } else {
      return true;
    }
  }
  
  // Add entry to $this->clusters for $cluster_name if one doesn't already exist.
  private function initCluster($cluster_name) {
    if(!isSet($this->clusters[$cluster_name])) {
      $this->clusters[$cluster_name] = array('private'=>false,'users'=>array());
    }
  }
  
  // Return true if user exists and password matches.
  private function authenticate() {
    $user = $this->getUser();
    $password = $this->getPassword();
    if(empty($user) || empty($password)) {
      $this->requireHttpCredentials();
    }
    if(!isSet($this->users[$user])) {
      return false;
    }
    return sha1($password) == $this->users[$user];
  }
  
  // Send authentication headers and terminate execution.
  private function requireHttpCredentials() {
    header("WWW-authenticate: basic realm=\"Ganglia\"");
    header("HTTP/1.0 401 Unauthorized");
    exit;
  }
  
  private function getUser() {
    return isSet($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : null;
  }
  
  private function getPassword() {
    return isSet($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : null;
  }
}
?>

