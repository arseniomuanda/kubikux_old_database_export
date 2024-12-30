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

    public function index($firma = 6)
    {
        if(is_null($firma)){
            return $this->respond([
                "error" => true,
                "message" => 'Firma não encontrada!'
            ]);
        }

        $single_firm = $this->db->query("select * from firma where id = $firma")->getRow();
        
        
            #Years
            $single_firm->years = $this->db
                ->query("SELECT * FROM `ano` WHERE firma = {$single_firm->id}")
                ->getResult();

            #Patrimonies
            $single_firm->patrimonies = $this->db
                ->query("SELECT * FROM `patrimonio` WHERE firma = {$single_firm->id}")
                ->getResult();

            #Tipo documentos
            $all_ducument_pypes = $this->db
                ->query("SELECT * FROM `tipodocumento` WHERE firma = {$single_firm->id}")
                ->getResult();
            
            
            ## Get each document
            $final_all_ducument_pypes = array();
            foreach($all_ducument_pypes as $single_document_type){
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
            $all_centro_custos = $this->db
                ->query("SELECT * FROM `centrocustos` WHERE firma = {$single_firm->id}")
                ->getResult();

            ##Get each centro de custo
            $final_all_centro_custos = array();
            foreach ($all_centro_custos as $single_centro_custo) {
                $single_centro_custo->expenses = $this->db
                    ->query("SELECT * FROM `despesa` WHERE centrocusto = {$single_centro_custo->id}")
                    ->getResult();

                array_push($final_all_centro_custos, $single_centro_custo);
            }
            $single_firm->centro_custos = $final_all_centro_custos;


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

                    #Pagamentos
                    $all_payments = $this->db
                        ->query("SELECT * FROM `pagamento` WHERE morador = {$single_apt->id}")
                        ->getResult();

                    ##Get each payment
                    $final_all_payments = array();
                    foreach ($all_payments as $single_payment) {
                        #payment_items
                        $single_payment->items = $this->db
                            ->query("SELECT * FROM `itempagamento` WHERE pagamento = {$single_payment->id}")
                            ->getResult();

                        #pagamento_recibos
                        $single_payment->normal_recibos = $this->db
                            ->query("SELECT * FROM `recibo_pagamento` WHERE pagamento = {$single_payment->id}")
                            ->getResult();

                        #pagamento_recibos
                        $single_payment->mobile_recibos = $this->db
                            ->query("SELECT * FROM `recibo` WHERE id_pagamento = {$single_payment->id}")
                            ->getResult();

                        array_push($final_all_payments, $single_payment);
                    }
                    $single_apt->payments = $final_all_payments;

                    #Occurrences
                    $single_apt->occurrences = $this->db
                        ->query("SELECT * FROM `ocorrencia` WHERE morador = {$single_apt->id}")
                        ->getResult();

                    #Visits
                    $single_apt->visits = $this->db
                        ->query("SELECT * FROM `visitante` WHERE moradorvisitado = {$single_apt->id}")
                        ->getResult();
                }
            
            $single_firm; 
        

        // Configuração para baixar JSON
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="firmas.json"');
        echo json_encode($single_firm, JSON_PRETTY_PRINT);
        exit;
    }
}