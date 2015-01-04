<?php

/**
 * Domain Api of AlternC, used by alternc-api package
 */
class Alternc_Api_Object_Domain {
    
  const ERR_INVALID_ARGUMENT = 1115401;
  const ERR_ALTERNC_FUNCTION = 1115402;
  const ERR_NOT_FOUND = 1115403;
  
  var $admin; // m_admin instance
  var $dom; // m_dom instance
  var $cuid; // current user id
  var $isAdmin; // is it an Admin account?
  
  function __construct($service) {
    global $admin,$cuid,$dom;
    if (!($service instanceof Alternc_Api_Service)) {
      throw new \Exception("Bad argument: service is not an Alternc_Api_Service", self::ERR_INVALID_ARGUMENT);
    }
    // We store the global $cuid to AlternC legacy classes
    $this->cuid=$cuid=$service->token->uid;
    $this->isAdmin=$service->token->isAdmin;
    // We use the global $admin & $dom from AlternC legacy classes
    $this->admin=$admin;
    $this->dom=$dom;
  }

  
  /** API Method from legacy class method dom->get_domain_list()
   * @param $options a hash with parameters transmitted to legacy call
   * may be "uid" to only return domains for a specific user-id
   * (if you are not admin, this WILL only list YOUR domains anyway)
   * may be "offset" and/or "count" to do paging.
   * @return Alternc_Api_Response whose content is the list of hosted domains on this server
   */
  function find($options) {      
      global $cuid;
      $sql="";
      if ($this->isAdmin) {
        if (isset($options["uid"])) {
            $uid=intval($options["uid"]);
        } else {
            $uid=-1;
        }
      } else {
        $uid=$cuid;
      }
      $result=$this->dom->get_domain_list($uid);
      if (!$result) {
          return $this->alterncLegacyErrorManager();
      } else {
          $offset=-1; $count=-1;
          if (isset($options["count"])) $count=intval($options["count"]);
          if (isset($options["offset"])) $offset=intval($options["offset"]);
          if ($offset!=-1 || $count!=-1) {
              if ($offset<0 || $offset>count($result)) $offset=0;
              if ($count<0 || $count>1000) $count=1000;
              $result=  array_slice($result, $offset, $count);
          }
          return new Alternc_Api_Response( array("content" =>$result) );
      }
  }
  


  /** return a proper Alternc_Api_Response from an error class and error string 
   * from AlternC legacy class
   */
  private function alterncLegacyErrorManager() {
    global $err;
    return new Alternc_Api_Response( array("code" => self::ERR_ALTERNC_FUNCTION, "message" => "[".$err->clsid."] ".$err->error) );
  }


} // class Alternc_Api_Object_Ssl