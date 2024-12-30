<?php

namespace App\Controllers;

use Config\Database;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\api\ResponseTrait;

class Home extends ResourceController
{
    protected $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function index()
    {
        $restult = $this->db->query("select * from firma")->getResult();
        $final = array();
        
        foreach($restult as $single_firm){
            #Fontes de anos
            $single_firm->years = $this->db
                ->query("SELECT * FROM `ano` WHERE firma = {$single_firm->id}")
                ->getResult();

            #Fontes de anos
            $single_firm->years = $this->db
                ->query("SELECT * FROM `ano` WHERE firma = {$single_firm->id}")
                ->getResult();

            #Tipo documentos
            $all_ducument_pypes = $this->db
                ->query("SELECT * FROM `tipodocumento` WHERE firma = {$single_firm->id}")
                ->getResult();
            
            
            ## Get each document
            $final_all_ducument_pypes = array();
            foreach($single_firm->fonte_receitas as $single_document_type){
                $single_document_type->documents = $this->db
                    ->query("SELECT * FROM `documento` WHERE tipo = {$single_document_type->id}")
                    ->getResult();
                array_push($final_all_ducument_pypes, $single_document_type);
            }
            $single_firm->document_types = $final_all_ducument_pypes;


            #Fontes de receita
            $single_firm->fonte_receitas = $this->db
                ->query("SELECT * FROM `fontesreceita` WHERE firma = {$single_firm->id}")
                ->getResult();

            #Centros de custo
            $single_firm->centro_custos = $this->db
            ->query("SELECT * FROM `centrocustos` WHERE firma = {$single_firm->id}")
            ->getResult();

            #Apartamentos e moradores
            $single_firm->apartamentos = $this->db
                ->query("SELECT * FROM `morador` WHERE firma =
                {$single_firm->id}")
                ->getResult();

                foreach ($single_firm->apartamentos as $single_apt) {
                    #agregado familiar
                    $single_apt->agregados = $this->db
                        ->query("SELECT * FROM `agregado` WHERE codigomorador = {$single_apt->id}")
                        ->getResult();

                    #Viatuas
                    $single_apt->viatuas = $this->db
                    ->query("SELECT * FROM `carro` WHERE codigomorador = {$single_apt->id}")
                    ->getResult();

                    #Empregados
                    $single_apt->empregados = $this->db
                    ->query("SELECT * FROM `empregado` WHERE codigomorador = {$single_apt->id}")
                    ->getResult();
                }
            
            array_push($final, $single_firm); 
        }

        return $this->respond($final);
        return view('welcome_message', [
            'firmas' => $final
        ]);
    }
}
