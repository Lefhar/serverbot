<?php

namespace App\library;


use App\Repository\IdentificationRepository;
use SpecShaper\EncryptBundle\Encryptors\EncryptorInterface;

class IppowerLibrary
{
    private $ippower;
    private EncryptorInterface $encryptor;
    public function __construct(IdentificationRepository $identificationRepository,EncryptorInterface $encryptorinterface)
    {
        $this->ippower = $identificationRepository->findOneBy(['type'=>'ippower']);
        $this->encryptor = $encryptorinterface;
    }


    private  function getCurl($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $data = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $data;
    }

    public function getIppower()
    {
        return $this->ippower;
    }

    public function etat($pc)
    {

        $url = 'http://'.$this->encryptor->decrypt($this->getIppower()->getName()).':'.$this->encryptor->decrypt($this->getIppower()->getPassword()).'@power.serverbot.fr:122/Set.cmd?CMD=GetPower';
        $data = $this->getCurl($url);
        preg_match('/<html>(.*?)<\/html>/s', $data, $match);

//        $teste = str_replace('=','=>',$match[1]);
//        dump([$teste]);
        parse_str(str_replace(',', '&', $match[1]), $output);

        //teste

        if($output['p6'.$pc]==1){
            $resultat = 'Actif';
        }else{
            $resultat = 'Inactif';
        }
        return $resultat;
    }

  public function restart($pc)
    {
        ini_set('max_execution_time', 0);
        $url = 'http://'.$this->encryptor->decrypt($this->getIppower()->getName()).':'.$this->encryptor->decrypt($this->getIppower()->getPassword()).'@power.serverbot.fr:122/Set.cmd?CMD=SetPower+P6'.$pc.'=0';

        $this->getCurl($url);
        sleep(28);
        $url = 'http://'.$this->encryptor->decrypt($this->getIppower()->getName()).':'.$this->encryptor->decrypt($this->getIppower()->getPassword()).'@power.serverbot.fr:122/Set.cmd?CMD=SetPower+P6'.$pc.'=0';
      $start =  $this->getCurl($url);
        preg_match('/<html>(.*?)<\/html>/s', $start, $match);
        $retour = $match[1].',';
        parse_str(str_replace(',', '&', $match[1]), $output);
        if($output['p6'.$pc]==1){
            $resultat = 'Actif';
        }else{
            $resultat = 'Inactif';
        }
        return $resultat;
    }


}