<?php

namespace App\Controller;

use App\Entity\Ssh;
use App\Form\SshType;
use App\Repository\SshRepository;
use SpecShaper\EncryptBundle\Encryptors\EncryptorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use SpecShaper\EncryptBundle\Event\EncryptEvent;
use SpecShaper\EncryptBundle\Event\EncryptEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @Route("/admin/ssh")
 */
class SshController extends AbstractController
{
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

    public function ssh2_debug($message, $language, $always_display)
    {
        printf("%s %s %s\n", $message, $language, $always_display);
    }

    /* Notify the user if the server terminates the connection */
    public function my_ssh_disconnect($reason, $message, $language)
    {
        printf("Server disconnected with reason code [%d] and message: %s\n", $reason, $message);
    }

    public function connexionSShFreenas($identifiant, $password, $ip, $port)
    {
        $methods = array(
            'hostkey' => 'ssh-rsa,ssh-dss',
//    'kex' => 'diffie-hellman-group-exchange-sha256',
            'client_to_server' => array(
                'crypt' => 'aes256-ctr,aes192-ctr,aes128-ctr,aes256-cbc,aes192-cbc,aes128-cbc,3des-cbc,blowfish-cbc',
                'comp' => 'none'),
            'server_to_client' => array(
                'crypt' => 'aes256-ctr,aes192-ctr,aes128-ctr,aes256-cbc,aes192-cbc,aes128-cbc,3des-cbc,blowfish-cbc',
                'comp' => 'none'));

        $callbacks = array('disconnect' => 'my_ssh_disconnect');

        foreach(array($ip) as $host) {
            $connection = ssh2_connect($host, $port, $methods, $callbacks);
            if (!$connection) die("Connection failed:");

            ssh2_auth_password($connection, $identifiant, $password) or die("Unable to authenticate");
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
            $stream_out5 = ssh2_fetch_stream($stream5, SSH2_STREAM_STDIO);
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
//var_dump($dftotalgiga0);
            $disktotal = $dftotalgiga0[30] / 1024000;
            $disktotal = number_format($disktotal, 2, ',', ' ');
            $diskuse = $dftotalgiga0[31] / 1024000;
            $diskuse = number_format($diskuse, 2, ',', ' ');
            $diskfree = $dftotalgiga0[32] / 1024000;
            $diskfree = number_format($diskfree, 2, ',', ' ');
            $memoryphy = trim($memoryphy1) / 1024000000;
            $memoryuse = trim($memoryuse1) / 1024000000;
            $ramcomplet = $memoryphy;
            $ramcomplet = number_format($ramcomplet, 2, ',', ' ');
            $memoryphy = number_format($memoryphy, 2, ',', ' ');
            $ramutil = number_format($memoryuse, 2, ',', ' ');
            $ramdispo = $ramcomplet - $ramutil;
            $ramdispo = number_format($ramdispo, 2, ',', ' ');
            $swapcomplet = $this->extstres22($swapbrut, 'Swap:', 'Total');
            $swapdispo = $this->extstres22($swapbrut, '' . trim($swapcomplet) . ' Total,', 'Free');
            $swaputil = $swapcomplet - $swapdispo;
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
            header('Access-Control-Allow-Origin: *');
            header('Content-Type: application/json');
            $contenu_json = json_encode($finaljson);
            $contenu_json = str_replace('\\', "", $contenu_json);
            // echo $contenu_json;

            return $this->json($contenu_json);
        }
    }


    /**
     * @Route("/sshjson/{id}", name="app_ssh_json", methods={"GET"})
     */
    public function sshacces(Ssh $id, EncryptorInterface $encryptor): Response
    {
        $jsonprocess = $this->connexionSShFreenas($encryptor->decrypt($id->getIdentifiant()), $encryptor->decrypt($id->getMotdepasse()), $id->getPort(), $id->getServer()->getIpv4());
        return $this->render('ssh/index.html.twig', [
            'sshes' => $jsonprocess,
        ]);
    }

    /**
     * @Route("/", name="app_ssh_index", methods={"GET"})
     */
    public function index(SshRepository $sshRepository): Response
    {
        return $this->render('ssh/index.html.twig', [
            'sshes' => $sshRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="app_ssh_new", methods={"GET", "POST"})
     */
    public function new(Request $request, SshRepository $sshRepository, EncryptorInterface $encryptor): Response
    {
        $ssh = new Ssh();
        $form = $this->createForm(SshType::class, $ssh);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $encrypted = $encryptor->encrypt('abcd');
            $decrypted = $encryptor->decrypt($encrypted);


            $ssh->setIdentifiant($encryptor->encrypt($form->get('identifiant')->getData()));
            $ssh->setMotdepasse($encryptor->encrypt($form->get('motdepasse')->getData()));
            dump($encrypted);
            dump($decrypted);
            $sshRepository->add($ssh, true);

            return $this->redirectToRoute('app_ssh_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('ssh/new.html.twig', [
            'ssh' => $ssh,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_ssh_show", methods={"GET"})
     */
    public function show(Ssh $ssh): Response
    {
        return $this->render('ssh/show.html.twig', [
            'ssh' => $ssh,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_ssh_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Ssh $ssh, SshRepository $sshRepository, EncryptorInterface $encryptor): Response
    {
        $form = $this->createForm(SshType::class, $ssh);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ssh->setIdentifiant($encryptor->encrypt($form->get('identifiant')->getData()));
            $ssh->setMotdepasse($encryptor->encrypt($form->get('motdepasse')->getData()));
            $sshRepository->add($ssh, true);

            return $this->redirectToRoute('app_ssh_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('ssh/edit.html.twig', [
            'ssh' => $ssh,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_ssh_delete", methods={"POST"})
     */
    public function delete(Request $request, Ssh $ssh, SshRepository $sshRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $ssh->getId(), $request->request->get('_token'))) {
            $sshRepository->remove($ssh, true);
        }

        return $this->redirectToRoute('app_ssh_index', [], Response::HTTP_SEE_OTHER);
    }
}
