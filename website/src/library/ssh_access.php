<?php

namespace App\library;

use phpseclib3\Net\SSH2;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

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
        } else {
            return false;
        }
    }

    public function setMachine($machine)
    {
        $this->machine = $machine;
    }

    public function setIp($ip)
    {
        $this->ip = $ip;
    }

    public function setIdentifiant($identifiant)
    {
        $this->identifiant = $identifiant;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function setPort($port)
    {
        $this->port = $port;
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
        if ($this->getMachine() == "Freenas") {
            return $this->connexionfreenasSSh();
        } elseif ($this->getMachine() == "Debian") {
            return $this->connexiondebianSSh();
        } else {
            return (array('error' => 'aucune machine compatible'));
        }
    }

    public function connexionfreenasSSh()
    {


// http://php.net/manual/en/context.socket.php
        $opts = array(
            'socket' => array(
                'bindto' => '192.168.10.1',
            ),
        );
      //  $context = stream_context_create($opts);
        $socket = stream_socket_client('tcp://'.$this->getIp().':'.$this->getPort(), $errno, $errstr, ini_get('default_socket_timeout'), STREAM_CLIENT_CONNECT);

        $ssh = new SSH2($socket);
        $ssh->login($this->getIdentifiant(), $this->getPassword());

//       $uptime =  $ssh->exec('uptime');
//       $memory =  $ssh->exec('sysctl hw | egrep \'hw.(phys|user|real)\'');




        //top -w
        //freenas-boot/ROOT/11.3-U5






        //  $stream_out5 = ssh2_fetch_stream($stream5, SSH2_STREAM_STDIO);
        $upteste =  $ssh->exec('uptime');;
        $cpu =  $ssh->exec('ps aux | wc -l');
        $memory =  $ssh->exec('sysctl hw | egrep \'hw.(phys|user|real)\'');
        $disk = $ssh->exec('df /');
        $swapbrut = $ssh->exec( 'top -w');
        $testecc = $ssh->exec('top -b -n 1');
        $testecpu = $this->extstres22($upteste, 'load averages:', "\n");
        $uptimexxzza = explode("\n", $testecc);
        // dump($uptimexxzza);
        $uptimexxzz = explode(" ", $uptimexxzza[9]);

        $tabteste = array();
        foreach ($uptimexxzz as $row)
        {
            if($row !=""){
                $tabteste[] = $row;
            }

        }

        $cpuusage = str_replace('%', "", $tabteste[10]);
        $uptimexx = explode(", ", trim($testecpu));

        //echo stream_get_contents($stream_out);
        $pos[0] = strpos($upteste, 'load') + 14;
        $uptime[0] = substr($upteste, $pos[0]);
        $pos[0] = strpos($uptime[0], ',');
        $uptime[1] = substr($uptime[0], 0, $pos[0]);
        $memoryphy1 = $this->extstres22($memory, 'hw.physmem:', 'hw.usermem:');
        $memoryuse1 = $this->extstres22($memory, 'hw.usermem:', 'hw.realmem:');

        $dftotalgiga0 = explode(" ", $disk);
        $memoryuse1 = str_replace('\n', "", $memoryuse1);
        $dftotalgiga0 = array_map('intval', $dftotalgiga0);
        foreach ($dftotalgiga0 as $key => $val){

//dump(gettype($val));
            if ($val == 0 or $val <10){

                unset($dftotalgiga0[$key]);

            }
            if (gettype($val) == 'string'){

                unset($dftotalgiga0[$key]);

            }

        }
        sort($dftotalgiga0);
        $disktotal = $dftotalgiga0[2] / 1024000;
        $disktotal = number_format($disktotal, 2, ',', ' ');
        $diskuse = $dftotalgiga0[0] / 1024000;
        $diskuse = number_format($diskuse, 2, ',', ' ');
        $diskfree = $dftotalgiga0[1] / 1024000;
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

        $swaputil = 0;
        $finaljson = ['cpu' => trim($cpu), 'pcpu' => $uptimexx,'cpuusage'=>$cpuusage, 'ram' => rtrim($ramcomplet), 'ramfree' => rtrim($ramdispo), 'ramuse' => rtrim($ramutil), 'swap' => trim($swapcomplet), 'swapfree' => trim($swapdispo), 'swapuse' => trim($swaputil), 'disk' => trim($disktotal), 'diskfree' => trim($diskfree), 'diskuse' => trim($diskuse)];

        return $finaljson;
    }
    public function connexionfreenasSSh2()
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
            'hostkey' => 'ssh-ed25519, ecdsa-sha2-nistp256, ecdsa-sha2-nistp384, ecdsa-sha2-nistp521, rsa-sha2-256, rsa-sha2-512, ssh-rsa, ssh-dss',
//    'kex' => 'diffie-hellman-group-exchange-sha256',
            'client_to_server' => array(
                'crypt' => 'curve25519-sha256, curve25519-sha256@libssh.org, ecdh-sha2-nistp256, ecdh-sha2-nistp384, ecdh-sha2-nistp521, diffie-hellman-group-exchange-sha256, diffie-hellman-group-exchange-sha1, diffie-hellman-group14-sha256, diffie-hellman-group14-sha1, diffie-hellman-group15-sha512, diffie-hellman-group16-sha512, diffie-hellman_group17-sha512, diffie-hellman-group18-sha512, diffie-hellman-group1-sha1',
                'comp' => 'none'),
            'server_to_client' => array(
                'crypt' => 'curve25519-sha256, curve25519-sha256@libssh.org, ecdh-sha2-nistp256, ecdh-sha2-nistp384, ecdh-sha2-nistp521, diffie-hellman-group-exchange-sha256, diffie-hellman-group-exchange-sha1, diffie-hellman-group14-sha256, diffie-hellman-group14-sha1, diffie-hellman-group15-sha512, diffie-hellman-group16-sha512, diffie-hellman_group17-sha512, diffie-hellman-group18-sha512, diffie-hellman-group1-sha1',
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
        $stream6 = ssh2_exec($connection, 'top -b -n 1');
        //freenas-boot/ROOT/11.3-U5
        stream_set_blocking($stream, true);
        stream_set_blocking($stream2, true);
        stream_set_blocking($stream3, true);
        stream_set_blocking($stream4, true);
        stream_set_blocking($stream5, true);
        stream_set_blocking($stream6, true);
        $stream_out = ssh2_fetch_stream($stream, SSH2_STREAM_STDIO);
        $stream_out2 = ssh2_fetch_stream($stream2, SSH2_STREAM_STDIO);
        $stream_out3 = ssh2_fetch_stream($stream3, SSH2_STREAM_STDIO);
        $stream_out4 = ssh2_fetch_stream($stream4, SSH2_STREAM_STDIO);
        $stream_out5 = ssh2_fetch_stream($stream5, SSH2_STREAM_STDIO);
        $stream_out6 = ssh2_fetch_stream($stream6, SSH2_STREAM_STDIO);
        //  $stream_out5 = ssh2_fetch_stream($stream5, SSH2_STREAM_STDIO);
        $upteste = stream_get_contents($stream_out);
        $cpu = stream_get_contents($stream_out2);
        $memory = stream_get_contents($stream_out3);
        $disk = stream_get_contents($stream_out4);
        $swapbrut = stream_get_contents($stream5);
        $testecc = stream_get_contents($stream6);
        $testecpu = $this->extstres22($upteste, 'load averages:', "\n");
        $uptimexxzza = explode("\n", $testecc);
       // dump($uptimexxzza);
        $uptimexxzz = explode(" ", $uptimexxzza[9]);

        $tabteste = array();
        foreach ($uptimexxzz as $row)
        {
            if($row !=""){
                $tabteste[] = $row;
            }

        }

        $cpuusage = str_replace('%', "", $tabteste[10]);
        $uptimexx = explode(", ", trim($testecpu));

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

        $swaputil = 0;
        $finaljson = ['cpu' => trim($cpu), 'pcpu' => $uptimexx,'cpuusage'=>$cpuusage, 'ram' => rtrim($ramcomplet), 'ramfree' => rtrim($ramdispo), 'ramuse' => rtrim($ramutil), 'swap' => trim($swapcomplet), 'swapfree' => trim($swapdispo), 'swapuse' => trim($swaputil), 'disk' => trim($disktotal), 'diskfree' => trim($diskfree), 'diskuse' => trim($diskuse)];

        return $finaljson;
    }

    public function connexiondebianSSh()
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
        $stream3 = ssh2_exec($connection, 'cat /proc/meminfo');
        $stream4 = ssh2_exec($connection, 'df /');
        //top -w
        $stream5 = ssh2_exec($connection, '/usr/bin/top -b -n1');
        $stream6 = ssh2_exec($connection, 'vmstat -w 1');
        //$stream7 = ssh2_exec($connection, 'top');
        //freenas-boot/ROOT/11.3-U5
        stream_set_blocking($stream, true);
        stream_set_blocking($stream2, true);
        stream_set_blocking($stream3, true);
        stream_set_blocking($stream4, true);
        stream_set_blocking($stream5, true);
      //  stream_set_blocking($stream6, true);
       // stream_set_blocking($stream7, true);
        $stream_out = ssh2_fetch_stream($stream, SSH2_STREAM_STDIO);
        $stream_out2 = ssh2_fetch_stream($stream2, SSH2_STREAM_STDIO);
        $stream_out3 = ssh2_fetch_stream($stream3, SSH2_STREAM_STDIO);
        $stream_out4 = ssh2_fetch_stream($stream4, SSH2_STREAM_STDIO);
        $stream_out5 = ssh2_fetch_stream($stream5, SSH2_STREAM_STDIO);
        $stream_out6 = ssh2_fetch_stream($stream6, SSH2_STREAM_STDIO);

        $upteste = stream_get_contents($stream_out);
        $cpu = stream_get_contents($stream_out2);
        $memory = stream_get_contents($stream_out3);
        $disk = stream_get_contents($stream_out4);
        $testecuu = stream_get_contents($stream_out5);
        $testepourcentage = stream_get_contents($stream_out6);

        $uptimexxzza = explode("\n", trim($testecuu));
        $uptimexxzz = explode(" ", $uptimexxzza[7]);
        $tabteste = array();
        foreach ($uptimexxzz as $row)
        {
            if($row !=""){
                $tabteste[] = $row;
            }

        }
       // dump($uptimexxzza[6]);
       // dump($uptimexxzza[7]);
       // dd($uptimexxzz);
        //echo $upteste;
        //echo stream_get_contents($stream_out);
        $pos[0] = strpos($upteste, 'load') + 14;
        $uptime[0] = substr($upteste, $pos[0]);
        $pos[0] = strpos($uptime[0], ',');
          $uptimexx = explode("\n", $testepourcentage);
        $tabvaleur = explode(" ", $uptimexx[2]);

     //   dd( $tabteste);
        //dump(count($tabvaleur));
       // dump($dernierkey);
      //  dump($testepourcentage);
      //  $us = $tabteste[12];
       // $sy = $tabteste[13];
     //   dump($us+$sy);
      //  dd($uptimexxzz);
        $testecpu = $this->extstres22($upteste, 'load average:', "\n");
        //echo $upteste;

        $uptimexx = explode(", ", trim($testecpu));

        $tabprocess = array();
        $countrow = 0;
        foreach ($uptimexx as $row) {

            $rowi = str_replace("\n", "", $row);
            $rowi = str_replace(",", ".", $rowi);
            $countrow += (float)$rowi;
            $tabprocess[] = $rowi;
        }
        //  dump($tabprocess);
        // dd(array_sum($tabprocess));
        //  dd($tabprocess);
        $uptime[1] = substr($uptime[0], 0, $pos[0]);
        $uptime[1] = $uptimexx;
        $dftotalgiga0 = explode(" ", $disk);
  //      dump($dftotalgiga0);
        $dftotalgiga0 = array_map('intval', $dftotalgiga0);
        foreach ($dftotalgiga0 as $key => $val){

//dump(gettype($val));
            if ($val == 0 or $val <10){

                unset($dftotalgiga0[$key]);

            }
            if (gettype($val) == 'string'){

                unset($dftotalgiga0[$key]);

            }

        }
        sort($dftotalgiga0);
       // dump($dftotalgiga0);

        $disktotal = $dftotalgiga0[3] / 1024000;
        $disktotal = number_format($disktotal, 2, ',', ' ');
        $diskuse = $dftotalgiga0[2] / 1024000;


        $disklibre = $dftotalgiga0[1] / 1024000;
        $diskfree = number_format($disklibre, 2, ',', '');
      //  dump($disklibre);
        $diskuse = number_format($diskuse, 2, ',', ' ');


       // dump($memory);
        $mem = explode("\n", $memory);
     //   dump($mem);

     //   dump($disklibre);
       // $diskfree = number_format($disklibre, 2);

        $memoire = $mem[0];
        $memfree = $mem[1];
        $swap = $mem[14];
        $swapfree = $mem[15];
        $memoire = str_replace('MemTotal:', '', $memoire);
        $memoire = str_replace('kB', '', trim($memoire));
        $memfree = str_replace('MemFree:', '', $memfree);
        $memfree = str_replace('kB', '', trim($memfree));
        $swapfree = str_replace('SwapFree:', '', $swapfree);
        $swapfree = str_replace('kB', '', trim($swapfree));
        $swap = str_replace('SwapTotal:', '', $swap);
        $swap = str_replace('kB', '', trim($swap));
        //fin

        $memuse = (float)$memoire - (float)$memfree;
        $swapuse = (float)$swap - (float)$swapfree;
        $memoire1 = (float)$memoire / 1024000;
        $memfree1 = (float)$memfree / 1024000;
        $memuse1 = (float)$memuse / 1024000;


        $swap1 = (float)$swap / 1024000;
        $swapfree1 = (float)$swapfree / 1024000;
        $swapuse1 = (float)$swapuse / 1024000;

        $memuse2 = (int)$memuse1;
        $ramcomplet = number_format($memoire1, 2, ',', ' ');
        $ramdispo = number_format($memfree1, 2, ',', ' ');
        $ramutil = number_format($memuse1, 2, ',', ' ');

        $swapcomplet = number_format($swap1, 0, ',', ' ');
        $swapdispo = number_format($swapfree1, 0, ',', ' ');
        $swaputil = number_format($swapuse1, 0, ',', ' ');


        $finaljson = ['cpu' => trim($cpu),'cpuusage'=>$tabteste[8], 'pcpu' => $tabprocess, 'ram' => rtrim($ramcomplet), 'ramfree' => rtrim($ramdispo), 'ramuse' => rtrim($ramutil), 'swap' => trim($swapcomplet), 'swapfree' => trim($swapdispo), 'swapuse' => trim($swaputil), 'disk' => trim($disktotal), 'diskfree' => trim($diskfree), 'diskuse' => trim($diskuse)];

        return $finaljson;
    }
    public function reboot()
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

        $connection = ssh2_connect($this->getIp(), $this->getPort(), $methods, $callbacks);
        if (!$connection) die("Connection failed:");

        ssh2_auth_password($connection, $this->getIdentifiant(), $this->getPassword()) or die("Unable to authenticate");
        $stream = ssh2_exec($connection, 'shutdown -r now');


        stream_set_blocking($stream, true);

        $stream_out = ssh2_fetch_stream($stream, SSH2_STREAM_STDIO);


        $restart = stream_get_contents($stream_out);


        if(preg_match('`Shutdown NOW`i',$restart))
        {
             $result = true;
        }else{
            $result = false;
        }
        return $result;
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function ping($ip)
    {

        $ping = shell_exec("ping -c 1 $ip");
        if(preg_match('`100% packet loss`i', $ping))
        {
            $pinger = 'Hors ligne';
        }
        else
        {
            $pinger = 'En ligne';
        }
// echo $ping;
        return $pinger;
    }
}