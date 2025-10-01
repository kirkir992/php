<?php
// ad_auth.php - Active Directory Authentication Class
class ADAuth {
    private $conn;
    private $config;
    
    public function __construct($config) {
        $this->config = $config;
    }
    
    public function connect() {
        $this->conn = ldap_connect($this->config['host'], $this->config['port']);
        if (!$this->conn) {
            throw new Exception('Could not connect to AD server');
        }
        
        // Set LDAP options
        ldap_set_option($this->conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($this->conn, LDAP_OPT_REFERRALS, 0);
        
        if ($this->config['use_tls']) {
            if (!ldap_start_tls($this->conn)) {
                throw new Exception('Could not start TLS');
            }
        }
        
        return true;
    }
    
    public function authenticate($username, $password) {
        if (empty($username) || empty($password)) {
            return false;
        }
        
        try {
            $this->connect();
            
            // Bind with user credentials
            $user_dn = $this->getUserDN($username);
            $bind = @ldap_bind($this->conn, $user_dn, $password);
            
            if ($bind) {
                $user_info = $this->getUserInfo($username);
                ldap_unbind($this->conn);
                return $user_info;
            }
            
            ldap_unbind($this->conn);
            return false;
            
        } catch (Exception $e) {
            error_log("AD Auth Error: " . $e->getMessage());
            return false;
        }
    }
    
    private function getUserDN($username) {
        // Try different DN formats
        $dn_formats = [
            "CN={$username},CN=Users," . $this->config['base_dn'],
            "{$username}@{$this->config['domain']}",
            "uid={$username}," . $this->config['base_dn']
        ];
        
        foreach ($dn_formats as $dn) {
            $test_bind = @ldap_bind($this->conn, $dn, 'dummy');
            if ($test_bind) {
                return $dn;
            }
        }
        
        // If specific DNs fail, try with user principal name
        return "{$username}@{$this->config['domain']}";
    }
    
    private function getUserInfo($username) {
        $search_filter = "(sAMAccountName={$username})";
        $attributes = array(
            'cn', 'givenname', 'sn', 'mail', 'samaccountname', 
            'memberof', 'department', 'title', 'telephonenumber'
        );
        
        $search = ldap_search($this->conn, $this->config['base_dn'], $search_filter, $attributes);
        $entries = ldap_get_entries($this->conn, $search);
        
        if ($entries['count'] > 0) {
            $user = $entries[0];
            return array(
                'username' => $user['samaccountname'][0],
                'fullname' => $user['cn'][0],
                'firstname' => isset($user['givenname'][0]) ? $user['givenname'][0] : '',
                'lastname' => isset($user['sn'][0]) ? $user['sn'][0] : '',
                'email' => isset($user['mail'][0]) ? $user['mail'][0] : '',
                'department' => isset($user['department'][0]) ? $user['department'][0] : '',
                'title' => isset($user['title'][0]) ? $user['title'][0] : '',
                'phone' => isset($user['telephonenumber'][0]) ? $user['telephonenumber'][0] : '',
                'groups' => $this->extractGroups($user)
            );
        }
        
        return false;
    }
    
    private function extractGroups($user) {
        $groups = array();
        if (isset($user['memberof'])) {
            for ($i = 0; $i < $user['memberof']['count']; $i++) {
                // Extract CN from group DN
                if (preg_match('/CN=([^,]+)/', $user['memberof'][$i], $matches)) {
                    $groups[] = $matches[1];
                }
            }
        }
        return $groups;
    }
    
    public function isUserInGroup($user_groups, $required_groups) {
        foreach ($required_groups as $group) {
            if (in_array($group, $user_groups)) {
                return true;
            }
        }
        return false;
    }
}
?>
