<?php


namespace App\Funcoes;

use App\Dbconnect\Dbconnect;
use Exception;
use PDO;


class Funcoes
{

    public static function AtualizaRegistro($REGISTRO)
    {
        // Validar a migração
        try {
            $up = (new Dbconnect('CENTRAL_ATIVIDADE'))->update(
                'REGISTRO = ' . $REGISTRO,
                [
                    'MIGRACAO' => 1,
                    'DATAMIGRACAO' => date("Y-m-d H:i:s")
                ]
            );

            return "SUCESSO UPDATE NO BANCO";
        } catch (Exception $e) {
            return 'ERRO UPDATE NO BANCO' . $e->getMessage();
        }
    }
}
