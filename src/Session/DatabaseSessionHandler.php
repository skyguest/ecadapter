<?php
namespace Skyguest\Ecadapter\Session;

use SessionHandlerInterface;
use Pimple\Container;

class DatabaseSessionHandler implements SessionHandlerInterface {

	protected $db;
	protected $config;
	protected $max_life_time;
	protected $app;
	protected $time;
	protected $exists;

	public function __construct($db, $config, Container $app) {
		$this->db = $db;
		$this->config = $config;
		$this->max_life_time = isset($config['lifetime']) ? $config['lifetime'] : 1800;
		$this->app = $app;
		$this->time = time();
	}
	
	public function close() {

		/* 随机对 sessions_data 的库进行删除操作 */
        if (mt_rand(0, 2) == 2)
        {
            $this->getDataTable()->where('expiry', '<', ($this->_time - $this->max_life_time))->delete();
        }

        if ((time() % 2) == 0)
        {
        	$this->getSessionTable()->where('expiry', '<', ($this->_time - $this->max_life_time))->delete();
        }

		return true;
	}

	public function destroy($session_id) {
		$this->getDataTable()->where('sesskey', $session_id)->delete();
		$this->getSessionTable()->where('sesskey', $session_id)->delete();
	}

	public function gc($lifetime)
    {
        $this->getDataTable()->where('expiry', '<', ($this->_time - $lifetime))->delete();
        $this->getSessionTable()->where('expiry', '<', ($this->_time - $lifetime))->delete();
    }

	public function open($savePath, $sessionName)
    {
        return true;
    }

	public function read($sessionId)
    {
    	$sessionId = substr($sessionId, 0, 32);
    	$session = $this->getSessionTable()->where('sesskey', $sessionId)->first();

        if (!empty($session))
        {
            if (!empty($session->data) && $this->time - $session->expiry <= $this->max_life_time)
            {
            	$this->exists = true;
                $this->session_expiry = $session->expiry;
                $this->session_md5    = md5($session->data);
                $result = unserialize($session->data);
                $result['user_id'] = $session->userid;
                $result['admin_id'] = $session->adminid;
                $result['user_name'] = $session->user_name;
                $result['user_rank'] = $session->user_rank;
                $result['discount'] = $session->discount;
                $result['email'] = $session->email;
                return serialize($result);
            }
            else
            {
                $session_data = $this->getDataTable()->where('sesskey', $sessionId)->first();
                if ( !empty($session_data) && !empty($session_data->data) && $this->time - $session_data->expiry <= $this->max_life_time)
                {
                	$this->exists = true;
                    $this->session_expiry = $session_data->expiry;
	                $this->session_md5    = md5($session_data->data);
	                $result = unserialize($session_data->data);
	                $result['user_id'] = $session->userid;
	                $result['admin_id'] = $session->adminid;
	                $result['user_name'] = $session->user_name;
	                $result['user_rank'] = $session->user_rank;
	                $result['discount'] = $session->discount;
	                $result['email'] = $session->email;
	                return serialize($result);
                }
            }
        }
    }

	public function write($sessionId, $data)
    {
    	$save_sessionId = substr($sessionId, 0, 32);
        $session = @unserialize($data);
        
        $adminid = isset($session['admin_id']) ? intval($session['admin_id']) : 0;
        $userid  = isset($session['user_id'])  ? intval($session['user_id'])  : 0;
        $user_name  = isset($session['user_name'])  ? trim($session['user_name'])  : 0;
        $user_rank  = isset($session['user_rank'])  ? intval($session['user_rank'])  : 0;
        $discount  = isset($session['discount'])  ? round($session['discount'], 2)  : 0;
        $email  = isset($session['email'])  ? trim($session['email']) : 0;
        unset($session['admin_id']);
        unset($session['user_id']);
        unset($session['user_name']);
        unset($session['user_rank']);
        unset($session['discount']);
        unset($session['email']);

        if (! $this->exists) {
            $this->read($sessionId);
        }

        if ($this->exists) {

            $data = serialize($session);

            if (isset($data{255}))
            {
                if ( $this->getDataTable()->where('sesskey', $save_sessionId)->first() ) {
                	$this->getDataTable()->update([
                		'sesskey' => $save_sessionId, 
                		'expiry' => $this->time, 
                		'data' => $data
                	]);
                } else {
                	$this->getDataTable()->insert([
                		'sesskey' => $save_sessionId, 
                		'expiry' => $this->time, 
                		'data' => $data
                	]);
                }
                $data = '';
            }

            $this->getSessionTable()->where('sesskey', $save_sessionId)->update([
            	'expiry' => $this->time,
            	'ip' => $this->real_ip(),
            	'userid' => $userid,
            	'adminid' => $adminid,
            	'user_name' => $user_name,
            	'user_rank' => $user_rank,
            	'discount' => $discount,
            	'email' => $email,
            	'data' => $data
            ]);

        } else {
            
            $this->getSessionTable()->insert([
            	'sesskey' => $save_sessionId,
            	'expiry' => $this->time,
            	'ip' => $this->real_ip(),
            	'data' => 'a:0:{}'
            ]);
        }

        $this->exists = true;
    }

	private function getDataTable() {
		return $this->db->table('sessions_data');
	}

	private function getSessionTable() {
		return $this->db->table('sessions');
	}

	public function real_ip()
	{
	    static $realip = NULL;

	    if ($realip !== NULL)
	    {
	        return $realip;
	    }

	    if (isset($_SERVER))
	    {
	        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
	        {
	            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

	            /* 取X-Forwarded-For中第一个非unknown的有效IP字符串 */
	            foreach ($arr AS $ip)
	            {
	                $ip = trim($ip);

	                if ($ip != 'unknown')
	                {
	                    $realip = $ip;

	                    break;
	                }
	            }
	        }
	        elseif (isset($_SERVER['HTTP_CLIENT_IP']))
	        {
	            $realip = $_SERVER['HTTP_CLIENT_IP'];
	        }
	        else
	        {
	            if (isset($_SERVER['REMOTE_ADDR']))
	            {
	                $realip = $_SERVER['REMOTE_ADDR'];
	            }
	            else
	            {
	                $realip = '0.0.0.0';
	            }
	        }
	    }
	    else
	    {
	        if (getenv('HTTP_X_FORWARDED_FOR'))
	        {
	            $realip = getenv('HTTP_X_FORWARDED_FOR');
	        }
	        elseif (getenv('HTTP_CLIENT_IP'))
	        {
	            $realip = getenv('HTTP_CLIENT_IP');
	        }
	        else
	        {
	            $realip = getenv('REMOTE_ADDR');
	        }
	    }

	    preg_match("/[\d\.]{7,15}/", $realip, $onlineip);
	    $realip = !empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';

	    return $realip;
	}
}