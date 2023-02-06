<!DOCTYPE html>
<html lang="pt-br">

<?php

require_once "vendor/autoload.php";

use App\Dbconnect\Dbconnect;
use App\Funcoes\Funcoes;


$where = "VALIDACAO = 1 AND MIGRACAO IS NULL AND RA = 201410007777";


$con = (new Dbconnect())->select_webservice($where)
    ->fetchAll(PDO::FETCH_ASSOC);


//==================PAGINAÇÃO==================

// Numero de itens que vai aparecer na pagina
$itens_por_pagina = 5;

@$pagina = empty(filter_var(intval($_GET['pagina']), FILTER_SANITIZE_ADD_SLASHES)) ? 1 : filter_var(intval($_GET['pagina']), FILTER_SANITIZE_ADD_SLASHES);

$dadoPaginacao = (new Dbconnect('CENTRAL_ATIVIDADE'))->selectPaginacao($where, $pagina, $itens_por_pagina)
    ->fetchAll(PDO::FETCH_ASSOC);

$num_total = ceil(count($con) / $itens_por_pagina);

//==================PAGINAÇÃO==================


include('includes/head.php');


?>


<body>

    <nav class="navbar">
        <div class="container">
            <span class="text-light">
                <a class="navbar-brand" href="#">
                    <img src="assets/img/icon.png" class="img-logo" alt="Bootstrap" width="60" height="60">
                </a> | Webservice
            </span>
        </div>
    </nav>

    <div class="container">
        <div class="row">


            <div class="p-0 col-md-4">
                <h4 class="mt-5 mb-4">Número de atividades: <?= count($con) ?> </h4>
            </div>
            <div class="p-0 col-md-8 text-end">

                <a href="./logs/logs.txt" id="baixar" class=" mt-5 mb-4 btn btn-success" download="./logs/logs.txt">Ver Logs <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-filetype-txt" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M14 4.5V14a2 2 0 0 1-2 2h-2v-1h2a1 1 0 0 0 1-1V4.5h-2A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v9H2V2a2 2 0 0 1 2-2h5.5L14 4.5ZM1.928 15.849v-3.337h1.136v-.662H0v.662h1.134v3.337h.794Zm4.689-3.999h-.894L4.9 13.289h-.035l-.832-1.439h-.932l1.228 1.983-1.24 2.016h.862l.853-1.415h.035l.85 1.415h.907l-1.253-1.992 1.274-2.007Zm1.93.662v3.337h-.794v-3.337H6.619v-.662h3.064v.662H8.546Z" />
                    </svg></a>
                <a href="migrar.php" class=" mt-5 mb-4 btn btn-primary">Migrar <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cloud-arrow-up" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M7.646 5.146a.5.5 0 0 1 .708 0l2 2a.5.5 0 0 1-.708.708L8.5 6.707V10.5a.5.5 0 0 1-1 0V6.707L6.354 7.854a.5.5 0 1 1-.708-.708l2-2z" />
                        <path d="M4.406 3.342A5.53 5.53 0 0 1 8 2c2.69 0 4.923 2 5.166 4.579C14.758 6.804 16 8.137 16 9.773 16 11.569 14.502 13 12.687 13H3.781C1.708 13 0 11.366 0 9.318c0-1.763 1.266-3.223 2.942-3.593.143-.863.698-1.723 1.464-2.383zm.653.757c-.757.653-1.153 1.44-1.153 2.056v.448l-.445.049C2.064 6.805 1 7.952 1 9.318 1 10.785 2.23 12 3.781 12h8.906C13.98 12 15 10.988 15 9.773c0-1.216-1.02-2.228-2.313-2.228h-.5v-.5C12.188 4.825 10.328 3 8 3a4.53 4.53 0 0 0-2.941 1.1z" />
                    </svg></a>
            </div>


            <table class="table mt-3">
                <thead>
                    <tr>
                        <th scope="col"></th>
                        <th scope="col">RA</th>
                        <th scope="col">DESCRIÇÃO</th>
                        <th scope="col">DATA INÍCIO</th>
                        <th scope="col">DATA FIM</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dadoPaginacao as $linha) : ?>

                        <?php extract($linha); ?>

                        <tr>
                            <td><?= $linha ?></td>
                            <td><?= $RA ?></td>
                            <td><?= $DESCRICAO ?></td>
                            <td><?php echo str_replace(' 00:00:00.000', '', $DATAINICIO) ?></td>
                            <td><?php echo str_replace(' 00:00:00.000', '', $DATAFIM) ?></td>
                        </tr>

                    <?php endforeach; ?>
                </tbody>
            </table>


            <div class="d-flex justify-content-center">
                <nav aria-label="..." class="mt-4">
                    <ul class="pagination pagination-sm">
                        <?php for ($i = 0; $i < $num_total; $i++) { ?>
                            <li class="page-item"><a class="page-link" href="flont.php?pagina=<?= $i + 1 ?>"><?= $i + 1 ?></a></li>
                        <?php } ?>
                    </ul>
                </nav>
            </div>


        </div>
    </div>

</body>

</html>