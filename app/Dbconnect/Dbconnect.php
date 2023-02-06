<?php

namespace App\Dbconnect;

use PDOException;
use PDO;


class Dbconnect
{
    //Constantes de conexão com Db
    const SERVER = "";
    const DATABASE_NAME = "";
    const USER = "";
    const PASSWORD = "";

    //Variavel tabela
    private $tabela;


    /**
     * Conexão db
     * @var PDO
     */
    private $conexao;


    /**
     * Função define a tabela e instancia de conexão
     * 1 Parametro
     * @var string tabela
     */
    public function __construct($tabela = null)
    {
        $this->tabela = $tabela;
        $this->setConnection();
    }


    /**
     * Função que efetua a conexão com Db
     * OBS: CONEXÃO COM SQLSERVER Necessita de drives pdo_sqlsrv
     * Link donwload: https://docs.microsoft.com/pt-br/sql/connect/php/download-drivers-php-sql-server?view=sql-server-ver16
     * Link video instalação: https://www.youtube.com/watch?v=7spsRgc6AtE 
     */
    private function setConnection()
    {
        try {
            $this->conexao = new PDO('sqlsrv:Server=' . self::SERVER . '; Database=' . self::DATABASE_NAME,  self::USER, self::PASSWORD);
            $this->conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("ERRO DE CONEXÃO {$e->getMessage()}");
            // die("ERRO: 405");
        }
    }


    /**
     * Função para execultar a query no Db
     * 2 Parametro 
     * @var string query
     * @var array parametros
     */
    public function exculte($values, $parametros = [])
    {
        try {
            $excQuery = $this->conexao->prepare($values);
            $excQuery->execute($parametros);
            return $excQuery;
        } catch (PDOException $e) {
            die("ERRO NA QUERY {$e->getMessage()}");
            // die("ERRO: 405");
        }
    }


    /**
     * Função Insert no Db
     * 1 Parametro 
     * @var array values insert
     */
    public function insert($values)
    {
        $fields = array_keys($values);
        $binds =  array_pad([], count($fields), '?');

        $query = 'INSERT INTO ' . $this->tabela . '(' . implode(',', $fields) . ') VALUES(' . implode(',', $binds) . ')';

        // var_dump($query);
        // die();

        $this->exculte($query, array_values($values));

        // return $this->connection->lastInsertId();
    }


    /**
     * Função Select no Db
     * 1 Parametro 
     * @var string where
     */
    public function select($where = null)
    {
        $query = 'SELECT * FROM ' . $this->tabela . ' WHERE ' . $where;
        // var_dump($query);
        // die();
        return $this->exculte($query);
    }

    /**
     * Função Select no Db
     * @var string where
     */
    public function select_all()
    {
        $query = 'SELECT * FROM ' . $this->tabela;
        return $this->exculte($query);
    }

    /**
     * Função Select dos dados corretos do webservice
     * @var string where
     */
    public function select_webservice($where)
    {
        $query = "SELECT TOP (20) CEA.REGISTRO, CEA.MIGRACAO, CEA.CODCOLIGADA, CEA.RA, CEA.CARGAHORARIA, CEA.DESCRICAO, 
        CEA.OBSERVACAO, CEA.CUMPRIUATIVIDADE, CEA.DOCUMENTACAOENTREGUE, CEA.INSCRICAOCONFIRMADA, CEA.DATAINICIO, CEA.DATAFIM, CEA.CODPERLET, 
        CEA.CODFILIAL, SHF.IDHABILITACAOFILIAL, SPL.IDPERLET, SCO.CODCOMPONENTE, SMO.CODMODALIDADE, SPL.CODTIPOCURSO
FROM CENTRAL_ATIVIDADE CEA (NOLOCK) INNER JOIN
     (
        SELECT	SHF.CODCOLIGADA, SHF.IDHABILITACAOFILIAL, SHF.CODHABILITACAO, SHF.CODGRADE, SHF.CODTIPOCURSO, SHF.CODCURSO, 
                SHF.CODFILIAL, STU.NOME
        FROM RM.CORPORERM.DBO.SHABILITACAOFILIAL   SHF (NOLOCK) INNER JOIN
             RM.CORPORERM.DBO.STURNO			   STU (NOLOCK) ON SHF.CODCOLIGADA = STU.CODCOLIGADA AND SHF.CODTURNO = STU.CODTURNO 
     ) SHF ON CEA.CODCOLIGADA = SHF.CODCOLIGADA AND CEA.CODHABILITACAO = SHF.CODHABILITACAO AND CEA.CODGRADE = SHF.CODGRADE AND 
              CEA.CODTIPOCURSO = SHF.CODTIPOCURSO AND CEA.TURNO = SHF.NOME AND CEA.CODCURSO = SHF.CODCURSO AND 
              CEA.CODFILIAL = SHF.CODFILIAL INNER JOIN
     RM.CORPORERM.DBO.SHABILITACAOFILIALPL SHP (NOLOCK) ON SHF.CODCOLIGADA = SHP.CODCOLIGADA AND 
                                                           SHF.IDHABILITACAOFILIAL = SHP.IDHABILITACAOFILIAL INNER JOIN
     RM.CORPORERM.DBO.SPLETIVO			   SPL (NOLOCK) ON SHP.CODCOLIGADA = SPL.CODCOLIGADA AND SHP.IDPERLET = SPL.IDPERLET AND 
                                                           CEA.CODPERLET = SPL.CODPERLET INNER JOIN
     RM.CORPORERM.DBO.SCOMPONENTE		 SCO (NOLOCK) ON CEA.CODCOLIGADA = SCO.CODCOLIGADA AND CEA.COMPONENTE = SCO.DESCRICAO AND 
                                                         CEA.CODTIPOCURSO = SCO.CODTIPOCURSO AND CEA.COMPONENTE = SCO.DESCRICAO OUTER APPLY
     (
        SELECT TOP 1 SMO.CODMODALIDADE
        FROM  RM.CORPORERM.DBO.SMODALIDADE SMO (NOLOCK)
        WHERE SCO.CODCOLIGADA = SMO.CODCOLIGADA AND SCO.CODCOMPONENTE = SMO.CODCOMPONENTE AND CEA.MODALIDADE = SMO.DESCRICAO		 
        ORDER BY CODMODALIDADE 
     )SMO 
WHERE " . $where;
        return $this->exculte($query);
    }


    /**
     * Função select para paginação somente em sqlserver
     * 2 Parametro 
     * @var strings $numero da pagina, $itens por pagina
     */
    public function selectPaginacao($where, $pagina, $itens_por_pagina)
    {
        $query = "
        DECLARE @PAGINA_ATUAL AS INT,
        @QTDE_REGISTROS AS INT,
        @SOMATORIO AS INT
        SET @PAGINA_ATUAL = $pagina
        SET @QTDE_REGISTROS = $itens_por_pagina
        
        SET @SOMATORIO = @QTDE_REGISTROS * (@PAGINA_ATUAL - 1)
        
SELECT @SOMATORIO, *
FROM  ( 
			SELECT TOP(20) ROW_NUMBER() OVER(ORDER BY CEA.REGISTRO DESC) AS linha, CEA.REGISTRO, CEA.MIGRACAO, CEA.CODCOLIGADA, CEA.RA, CEA.CARGAHORARIA, CEA.DESCRICAO, 
					CEA.OBSERVACAO, CEA.CUMPRIUATIVIDADE, CEA.DOCUMENTACAOENTREGUE, CEA.INSCRICAOCONFIRMADA, CEA.DATAINICIO, CEA.DATAFIM, CEA.CODPERLET, 
					CEA.CODFILIAL, SHF.IDHABILITACAOFILIAL, SPL.IDPERLET, SCO.CODCOMPONENTE, SMO.CODMODALIDADE, SPL.CODTIPOCURSO
			FROM CENTRAL_ATIVIDADE CEA (NOLOCK) INNER JOIN
				 (
					SELECT	SHF.CODCOLIGADA, SHF.IDHABILITACAOFILIAL, SHF.CODHABILITACAO, SHF.CODGRADE, SHF.CODTIPOCURSO, SHF.CODCURSO, 
							SHF.CODFILIAL, STU.NOME
					FROM RM.CORPORERM.DBO.SHABILITACAOFILIAL   SHF (NOLOCK) INNER JOIN
						 RM.CORPORERM.DBO.STURNO			   STU (NOLOCK) ON SHF.CODCOLIGADA = STU.CODCOLIGADA AND SHF.CODTURNO = STU.CODTURNO 
				 ) SHF ON CEA.CODCOLIGADA = SHF.CODCOLIGADA AND CEA.CODHABILITACAO = SHF.CODHABILITACAO AND CEA.CODGRADE = SHF.CODGRADE AND 
						  CEA.CODTIPOCURSO = SHF.CODTIPOCURSO AND CEA.TURNO = SHF.NOME AND CEA.CODCURSO = SHF.CODCURSO AND 
						  CEA.CODFILIAL = SHF.CODFILIAL INNER JOIN
				 RM.CORPORERM.DBO.SHABILITACAOFILIALPL SHP (NOLOCK) ON SHF.CODCOLIGADA = SHP.CODCOLIGADA AND 
																	   SHF.IDHABILITACAOFILIAL = SHP.IDHABILITACAOFILIAL INNER JOIN
				 RM.CORPORERM.DBO.SPLETIVO			   SPL (NOLOCK) ON SHP.CODCOLIGADA = SPL.CODCOLIGADA AND SHP.IDPERLET = SPL.IDPERLET AND 
																	   CEA.CODPERLET = SPL.CODPERLET INNER JOIN
				 RM.CORPORERM.DBO.SCOMPONENTE		 SCO (NOLOCK) ON CEA.CODCOLIGADA = SCO.CODCOLIGADA AND CEA.COMPONENTE = SCO.DESCRICAO AND 
																	 CEA.CODTIPOCURSO = SCO.CODTIPOCURSO AND CEA.COMPONENTE = SCO.DESCRICAO OUTER APPLY
				 (
					SELECT TOP 1 SMO.CODMODALIDADE
					FROM  RM.CORPORERM.DBO.SMODALIDADE SMO (NOLOCK)
					WHERE SCO.CODCOLIGADA = SMO.CODCOLIGADA AND SCO.CODCOMPONENTE = SMO.CODCOMPONENTE AND CEA.MODALIDADE = SMO.DESCRICAO		 
					ORDER BY CODMODALIDADE 
				 )SMO 
			WHERE $where

	    )A
WHERE linha BETWEEN 1 + @SOMATORIO AND @QTDE_REGISTROS + @SOMATORIO
ORDER BY REGISTRO DESC
        ";
        // echo "<pre>";
        // print_r($query);
        // die();
        return $this->exculte($query);
    }


    /**
     * Função delete no Db
     * 1 Parametro 
     * @var string where
     */
    public function delete($where)
    {
        $query = 'DELETE FROM ' . $this->tabela . ' WHERE ' . $where;
        // var_dump($query);
        // die();
        return $this->exculte($query);
    }


    /**
     * Função Select no Db
     * 2 Parametro 
     * @var array values @var string where
     */
    public function update($where = null, $values)
    {
        $binds = array_keys($values);

        $query = 'UPDATE ' . $this->tabela . ' SET ' . implode('=?,', $binds) . '=? WHERE ' . $where;
        $this->exculte($query, array_values($values));
        // var_dump($query);
        // die();
        return true;
        // echo $query;
    }
}
