<?php

require_once "vendor/autoload.php";

use App\Dbconnect\Dbconnect;
use App\Funcoes\Funcoes;

date_default_timezone_set("America/Sao_Paulo");


$con = (new Dbconnect())->select_webservice("VALIDACAO = 1 AND MIGRACAO IS NULL AND RA = 2014100077777")
    ->fetchAll(PDO::FETCH_ASSOC);


foreach ($con as $linha) {
    @$xml .= '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tot="http://www.totvs.com/">';
    $xml .= '<soapenv:Header/>';
    $xml .= '<soapenv:Body>';
    $xml .= '<tot:SaveRecord>';
    $xml .= '<tot:DataServerName>EduInscAlunoAtvOfertadaData</tot:DataServerName>';
    $xml .= '<tot:XML>';
    $xml .= '<SATIVIDADEALUNO>';
    $xml .= '<CODCOLIGADA>' . $linha['CODCOLIGADA'] . '</CODCOLIGADA>';
    $xml .= '<IDATIVIDADE>0</IDATIVIDADE>';
    $xml .= '<IDHABILITACAOFILIAL>' . $linha['IDHABILITACAOFILIAL'] . '</IDHABILITACAOFILIAL>';
    $xml .= '<RA>' . $linha['RA'] . '</RA>';
    $xml .= '<IDPERLET>' . $linha['IDPERLET'] . '</IDPERLET>';
    $xml .= '<IDOFERTA></IDOFERTA>';
    $xml .= '<CODCOMPONENTE>' . $linha['CODCOMPONENTE'] . '</CODCOMPONENTE>';
    $xml .= '<CODMODALIDADE>' . $linha['CODMODALIDADE'] . '</CODMODALIDADE>';
    $xml .= '<CARGAHORARIA>' . str_replace('.0000', '', $linha['CARGAHORARIA']) . '</CARGAHORARIA>';
    $xml .= '<CREDITOS></CREDITOS>';
    $xml .= '<DESCRICAO>' . $linha['DESCRICAO'] . '</DESCRICAO>';
    $xml .= '<DATA></DATA>';
    $xml .= '<OBSERVACAO>' . $linha['OBSERVACAO'] . '</OBSERVACAO>';
    $xml .= '<CUMPRIUATIVIDADE>' . $linha['CUMPRIUATIVIDADE'] . '</CUMPRIUATIVIDADE>';
    $xml .= '<DOCUMENTACAOENTREGUE>' . $linha['DOCUMENTACAOENTREGUE'] . '</DOCUMENTACAOENTREGUE>';
    $xml .= '<INSCRICAOCONFIRMADA>' . $linha['INSCRICAOCONFIRMADA'] . '</INSCRICAOCONFIRMADA>';
    $xml .= '<CODSTATUS></CODSTATUS>';
    $xml .= '<DATAINICIO>' . $linha['DATAINICIO'] . '</DATAINICIO>';
    $xml .= '<DATAFIM>' . $linha['DATAFIM'] . '</DATAFIM>';
    $xml .= '<NOMECURSO></NOMECURSO>';
    $xml .= '<NOMEHABILITACAO></NOMEHABILITACAO>';
    $xml .= '<NOMEGRADE></NOMEGRADE>';
    $xml .= '<NOMETURNO></NOMETURNO>';
    $xml .= '<CODPERLET>' . $linha['CODPERLET'] . '</CODPERLET>';
    $xml .= '<DESCCOMPONENTE></DESCCOMPONENTE>';
    $xml .= '<DESCMODALIDADE></DESCMODALIDADE>';
    $xml .= '<DESCOFERTADA></DESCOFERTADA>';
    $xml .= '<CODINST></CODINST>';
    $xml .= '<LOCAL></LOCAL>';
    $xml .= '<CONVENIO></CONVENIO>';
    $xml .= '<CARGAHORARIAATV>' . str_replace('.0000', '', $linha['CARGAHORARIA']) . '</CARGAHORARIAATV>';
    $xml .= '<CODTIPOPART></CODTIPOPART>';
    $xml .= '<CODFILIAL>' . $linha['CODFILIAL'] . '</CODFILIAL>';
    $xml .= '<CODPREDIO></CODPREDIO>';
    $xml .= '<CODBLOCO></CODBLOCO>';
    $xml .= '<CODSALA></CODSALA>';
    $xml .= '<CODUSUARIO></CODUSUARIO>';
    $xml .= '<CODPESSOA></CODPESSOA>';
    $xml .= '<NOMEALUNO></NOMEALUNO>';
    $xml .= '<CARGAHOR></CARGAHOR>';
    $xml .= '<CARGAHORGRADE></CARGAHORGRADE>';
    $xml .= '<CONTEUDO></CONTEUDO>';
    $xml .= '<CREDITO></CREDITO>';
    $xml .= '<DTFINAL></DTFINAL>';
    $xml .= '<DTFINALINSC></DTFINALINSC>';
    $xml .= '<DTINICIAL></DTINICIAL>';
    $xml .= '<DTINICIALINSC></DTINICIALINSC>';
    $xml .= '</SATIVIDADEALUNO>';
    $xml .= '</tot:XML>';
    $xml .= '<tot:Contexto>?</tot:Contexto>';
    $xml .= '</tot:SaveRecord>';
    $xml .= '</soapenv:Body>';
    $xml .= '</soapenv:Envelope>';
    $xml .= 'GoHorse';
}

//Explode para tranformar e array cada xml
$explode_array = explode('GoHorse', $xml);

//Remove o ultimo indice do array que e sempre vazio
array_pop($explode_array);


//Coloque a url do seu dataserver RM
$WsdlRM = "http://ip/seu_data_server";


//Array com as credencias para acessar o serviço do seu dataserver RM
$soapParams = [
    'login' => 'seu_user',
    'password' => 'sua_senha',
    'authentication' => SOAP_AUTHENTICATION_BASIC,
    'trace' => 1,
    'exceptions' => 0
];

try {
    //Função que abre a conexão soap no php 
    $client = new SoapClient($WsdlRM, $soapParams);

    echo '<h3>Conectou com sucesso</h3> <br>';


    //Esse for e foreach são adaptado pra o meu problema, então analize oque você quer fazer
    for ($i = 0; $i < count($con); $i++) {

        foreach ($con as $linha) {

            extract($linha);

            //Esse array contém os parâmetros que devem ser inseridos no method do dataserver (Obrigatório)
            $params = ['DataServerName' => 'Nome_do_seu_data_server', 'XML' => $explode_array[$i], 'Contexto' => "CODCOLIGADA=$CODCOLIGADA;CODFILIAL=$CODFILIAL;CODTIPOCURSO=$CODTIPOCURSO;CODSISTEMA=S"];
        }

        //Executa o method dentro do webservice (SaveRecord = Salvar detro do RM)
        $result = $client->SaveRecord($params);

        print_r('<br><br> <b>Resposta Data Server: </b> ' . $client->__getLastResponse() . ' <br>');

        $res_web = explode(';', $client->__getLastResponse());

        $var = substr($res_web[0], -1);
    }
    foreach ($con as $linha) {

        extract($linha);

        $arr = [$REGISTRO, $var];

        $mec = substr($arr[0] . '/' . $arr[1], -1);

        if ($mec == 1) {

            //Função que atualiza no banco o registro
            $updateDB = Funcoes::AtualizaRegistro($arr[0]);

            //LOG
            $dataLog = date('d/m/Y H:i:s');
            $arquivo = fopen('logs/logs.txt', 'a+', 0);

            $texto = "Data da importação: $dataLog - SUCESSO NA MIGRAÇÃO | $updateDB | REGISTRO = $arr[0] \n";

            fwrite($arquivo, $texto);
            fclose($arquivo);
            //FIM LOG
        } else {

            //LOG
            $dataLog = date('d/m/Y H:i:s');
            $arquivo = fopen('logs/logs.txt', 'a+', 0);

            $texto = "Data da importação: $dataLog - ERRO NA MIGRAÇÃO | REGISTRO = $arr[0] => RESPOSTA DATASERVER: " .  $client->__getLastResponse() . "\n";

            fwrite($arquivo, $texto);
            fclose($arquivo);
            //FIM LOG
        }
    }
    echo ("<script>
        alert('Importados');
        window.location = 'http://localhost/webservice/index.php';
        </script>");

    // echo '<pre>';
    // print_r($client->__getTypes());
} catch (SoapFault $e) {
    echo "Error!";
    echo $e->getMessage();
    echo 'Last response: ' . $client->__getLastResponse();
}
