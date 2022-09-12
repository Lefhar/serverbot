<?php

namespace App\library;

use App\Repository\IppowerRepository;
use SpecShaper\EncryptBundle\Encryptors\EncryptorInterface;

class IppowerLibrary
{


    public function etat($pc, EncryptorInterface $encryptor, IppowerRepository $ippowerRepository)
    {
        $ippower = $ippowerRepository->find(6);
        $url = 'http://'.$encryptor->decrypt($ippower->getName()).':'.$encryptor->decrypt($ippower->getPassword()).'@power.serverbot.fr:122/Set.cmd?CMD=GetPower';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $data = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        preg_match('/<html>(.*?)<\/html>/s', $data, $match);
        $extraire= explode(',',$match[0]);

        curl_close($ch);
        if($extraire['p6'.$pc]==1){
            $resultat = 'Actif';
        }else{
            $resultat = 'Inactif';
        }
        return $resultat;
    }


}