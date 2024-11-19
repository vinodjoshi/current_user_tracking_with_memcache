<?php

class LiveUsersTracker{
    private $memcache;
    private $expiry_time = 300;
    private $host = '127.0127.0.0.1';
    private $port = 11211;

    public function __construct()
    {
        $this->memcache = new Memcached();
        $this->memcache->addServer($host, $port);
        
        $this->track_user_activity();
    }

    public function track_user_activity() {

        $user_key = 'user_' . md5($_SERVER['REMOTE_ADDR']);

        // Update user activity timestamp
        $this->memcache->set($user_key, time(), $this->expiry_time);

        // Maintain a list of active users
        $active_users = $this->memcache->get('active_users');
        if (!$active_users) {
            $active_users = [];
        }

        if (!in_array($user_key, $active_users)) {
            $active_users[] = $user_key;
            $this->memcache->set('active_users', $active_users, $this->expiry_time);
        }
    }

    public function get_live_users_count()
    {
        $active_users = $this->memcache->get('active_users');
        $live_users_count = 0;

        if ($active_users) {
            foreach ($active_users as $key) {
                $last_activity = $this->memcache->get($key);
                if ($last_activity && (time() - $last_activity <= $this->expiry_time)) {
                    $live_users_count++;
                }
            }
        }

        return $live_users_count;
    }
}

$liveUserTracking = new LiveUsersTracker();

echo $liveUserTracking->get_live_users_count();

?>