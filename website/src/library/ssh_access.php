<?php

namespace App\library;

class ssh_access
{
    private $machine;
    private $ip;
    private $identifiant;
    private $password;
    private $port;
    public function extstres22($content, $start, $end)
    {
        if ((($content and $start) and $end)) {
            $r = explode($start, $content);
            if (isset($r[1])) {
                $r = explode($end, $r[1]);
                return $r[0];
            }
            return '';
        }
    }

    public function setMachine($machine)
    {
        $this->machine=$machine;
    }
    public function setIp($ip)
    {
        $this->ip=$ip;
    }
    public function setIdentifiant($identifiant)
    {
        $this->identifiant=$identifiant;
    }
    public function setPassword($password)
    {
        $this->password=$password;
    }
    public function setPort($port)
    {
        $this->port=$port;
    }

    public function getMachine()
    {
        return $this->machine;
    }
    public function getIp()
    {
        return $this->ip;
    }
    public function getPort()
    {
        return $this->port;
    }
    public function getIdentifiant()
    {
        return $this->identifiant;
    }
    public function getPassword()
    {
        return $this->password;
    }


    public function connexionSSh()
    {
        if($this->getMachine()=="Freenas"){
            return $this->connexionfreenasSSh();
        }else{
            return (array('error'=>'aucune machine compatible'));
        }
    }
    public function connexionfreenasSSh()
    {



        if (!function_exists("ssh2_connect")) die("function ssh2_connect doesn't exist");

        function ssh2_debug($message, $language, $always_display)
        {
            printf("%s %s %s\n", $message, $language, $always_display);
        }

        /* Notify the user if the server terminates the connection */
        function my_ssh_disconnect($reason, $message, $language)
        {
            printf("Server disconnected with reason code [%d] and message: %s\n", $reason, $message);
        }

        $methods = array(
            'hostkey' => 'ssh-rsa,ssh-dss',
//    'kex' => 'diffie-hellman-group-exchange-sha256',
            'client_to_server' => array(
                'crypt' => 'aes256-ctr,aes192-ctr,aes128-ctr,aes256-cbc,aes192-cbc,aes128-cbc,3des-cbc,blowfish-cbc',
                'comp' => 'none'),
            'server_to_client' => array(
                'crypt' => 'aes256-ctr,aes192-ctr,aes128-ctr,aes256-cbc,aes192-cbc,aes128-cbc,3des-cbc,blowfish-cbc',
                'comp' => 'none'));

  //      $callbacks = array('disconnect' => 'my_ssh_disconnect');
        $callbacks = array(
            1 => 'NET_SSH2_DISCONNECT_HOST_NOT_ALLOWED_TO_CONNECT',
            2 => 'NET_SSH2_DISCONNECT_PROTOCOL_ERROR',
            3 => 'NET_SSH2_DISCONNECT_KEY_EXCHANGE_FAILED',
            4 => 'NET_SSH2_DISCONNECT_RESERVED',
            5 => 'NET_SSH2_DISCONNECT_MAC_ERROR',
            6 => 'NET_SSH2_DISCONNECT_COMPRESSION_ERROR',
            7 => 'NET_SSH2_DISCONNECT_SERVICE_NOT_AVAILABLE',
            8 => 'NET_SSH2_DISCONNECT_PROTOCOL_VERSION_NOT_SUPPORTED',
            9 => 'NET_SSH2_DISCONNECT_HOST_KEY_NOT_VERIFIABLE',
            10 => 'NET_SSH2_DISCONNECT_CONNECTION_LOST',
            11 => 'NET_SSH2_DISCONNECT_BY_APPLICATION',
            12 => 'NET_SSH2_DISCONNECT_TOO_MANY_CONNECTIONS',
            13 => 'NET_SSH2_DISCONNECT_AUTH_CANCELLED_BY_USER',
            14 => 'NET_SSH2_DISCONNECT_NO_MORE_AUTH_METHODS_AVAILABLE',
            15 => 'NET_SSH2_DISCONNECT_ILLEGAL_USER_NAME'
        );

        $connection = ssh2_connect($this->getIp(), $this->port, $methods, $callbacks);
        if (!$connection) die("Connection failed:");

        ssh2_auth_password($connection, $this->getIdentifiant(), $this->getPassword()) or die("Unable to authenticate");
        $stream = ssh2_exec($connection, 'uptime');
        $stream2 = ssh2_exec($connection, 'ps aux | wc -l');
        $stream3 = ssh2_exec($connection, 'sysctl hw | egrep \'hw.(phys|user|real)\'');
        $stream4 = ssh2_exec($connection, 'df /');
        //top -w
        $stream5 = ssh2_exec($connection, 'top -w');
        //freenas-boot/ROOT/11.3-U5
        stream_set_blocking($stream, true);
        stream_set_blocking($stream2, true);
        stream_set_blocking($stream3, true);
        stream_set_blocking($stream4, true);
        stream_set_blocking($stream5, true);
        $stream_out = ssh2_fetch_stream($stream, SSH2_STREAM_STDIO);
        $stream_out2 = ssh2_fetch_stream($stream2, SSH2_STREAM_STDIO);
        $stream_out3 = ssh2_fetch_stream($stream3, SSH2_STREAM_STDIO);
        $stream_out4 = ssh2_fetch_stream($stream4, SSH2_STREAM_STDIO);
        //  $stream_out5 = ssh2_fetch_stream($stream5, SSH2_STREAM_STDIO);
        $upteste = stream_get_contents($stream_out);
        $cpu = stream_get_contents($stream_out2);
        $memory = stream_get_contents($stream_out3);
        $disk = stream_get_contents($stream_out4);
        $swapbrut = stream_get_contents($stream5);

        //echo $upteste;
        //echo stream_get_contents($stream_out);
        $pos[0] = strpos($upteste, 'load') + 14;
        $uptime[0] = substr($upteste, $pos[0]);
        $pos[0] = strpos($uptime[0], ',');
        $uptime[1] = substr($uptime[0], 0, $pos[0]);
        $memoryphy1 = $this->extstres22($memory, 'hw.physmem:', 'hw.usermem:');
        $memoryuse1 = $this->extstres22($memory, 'hw.usermem:', 'hw.realmem:');
        $dftotalgiga0 = explode(" ", $disk);
        $memoryuse1 = str_replace('\n', "", $memoryuse1);

        $disktotal = $dftotalgiga0[32] / 1024000;
        $disktotal = number_format($disktotal, 2, ',', ' ');
        $diskuse = $dftotalgiga0[33] / 1024000;
        $diskuse = number_format($diskuse, 2, ',', ' ');
        $diskfree = $dftotalgiga0[34] / 1024000;
        $diskfree = number_format($diskfree, 2, ',', ' ');
        $memoryphy = trim($memoryphy1) / 1024000000;
        $memoryuse = trim($memoryuse1) / 1024000000;
        $ramcomplet = $memoryphy;
        $ramcomplet = number_format($ramcomplet, 2, ',', ' ');
        $memoryphy = number_format($memoryphy, 2, ',', ' ');
        $ramutil = number_format($memoryuse, 2, ',', ' ');
        //    dump((int)$ramcomplet  );
        $ramdispo = (float)$ramcomplet - (float)$ramutil;

        $ramdispo = number_format($ramdispo, 2, ',', ' ');
        $swapcomplet = $this->extstres22($swapbrut, 'Swap:', 'Total');
        $swapdispo = $this->extstres22($swapbrut, '' . trim($swapcomplet) . ' Total,', 'Free');
        $swaputil = (float)$swapcomplet - (float)$swapdispo;
//$swapcomplet = 0;
//$swapdispo = 0;
        $swaputil = 0;
//sysctl hw | egrep 'hw.(phys|user|real)'
// echo 'load '.$uptime[1];
// echo '<br>';
// echo 'cpu'.$cpu;
// echo '<br>';
// echo 'ramcomplet'.$ramcomplet;
// echo '<br>';
// echo 'ramutil'.$ramutil;
// echo '<br>';
        $finaljson = ['cpu' => trim($cpu), 'pcpu' => trim($uptime[1]), 'ram' => rtrim($ramcomplet), 'ramfree' => rtrim($ramdispo), 'ramuse' => rtrim($ramutil), 'swap' => trim($swapcomplet), 'swapfree' => trim($swapdispo), 'swapuse' => trim($swaputil), 'disk' => trim($disktotal), 'diskfree' => trim($diskfree), 'diskuse' => trim($diskuse)];
        //   header('Access-Control-Allow-Origin: *');
        //     header('Content-Type: application/json');
        //  $contenu_json = json_encode($finaljson);
        //  $contenu_json = str_replace('\\', "", $contenu_json);
        //   return $contenu_json;
        return $finaljson;
    }


}