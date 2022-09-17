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

    public function getIppower()
    {
        return $this->ippower;
    }

    public function etat($pc)
    {

        $url = 'http://'.$this->encryptor->decrypt($this->getIppower()->getName()).':'.$this->encryptor->decrypt($this->getIppower()->getPassword()).'@power.serverbot.fr:122/Set.cmd?CMD=GetPower';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $data = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        preg_match('/<html>(.*?)<\/html>/s', $data, $match);

//        $teste = str_replace('=','=>',$match[1]);
//        dump([$teste]);
        parse_str(str_replace(',', '&', $match[1]), $output);

        //teste
        curl_close($ch);
        if($output['p6'.$pc]==1){
            $resultat = 'Actif';
        }else{
            $resultat = 'Inactif';
        }
        return $resultat;
    }


}