<?php

header('Content-Type: application/json');

$bilhetesFile = __DIR__ . '/dados/bilhetes.json';
$resultadoFile = __DIR__ . '/dados/resultado.json';

// GERAR BILHETES
function gerarBilhetes() {
    $bilhetes = [];

    for ($i = 1; $i <= 100; $i++) {
        $nums = [];

        while (count($nums) < 16) {
            $nums[] = rand(0,9);
        }

        $bilhetes[$i] = [
            "numeros" => $nums,
            "comprador" => null,
            "status" => "disponivel"
        ];
    }

    return $bilhetes;
}

// GARANTIR BILHETES
if (!file_exists($bilhetesFile)) file_put_contents($bilhetesFile, "[]");

$bilhetes = json_decode(file_get_contents($bilhetesFile), true);

if (!$bilhetes || count($bilhetes) == 0) {
    $bilhetes = gerarBilhetes();
    file_put_contents($bilhetesFile, json_encode($bilhetes, JSON_PRETTY_PRINT));
}

// GARANTIR RESULTADO
if (!file_exists($resultadoFile)) {
    file_put_contents($resultadoFile, json_encode([
        "numeros"=>[0,0,0,0],
        "vencedor"=>null
    ], JSON_PRETTY_PRINT));
}

$acao = $_GET['acao'] ?? '';

// LISTAR
if ($acao == 'listar') {
    echo json_encode(["bilhetes"=>$bilhetes]);
    exit;
}

// RESERVAR
if ($acao == 'reservar') {
    $id = $_POST['id'];
    $nome = $_POST['nome'];

    if ($bilhetes[$id]['status'] != "disponivel") exit;

    $bilhetes[$id]['comprador'] = $nome;
    $bilhetes[$id]['status'] = "reservado";

    file_put_contents($bilhetesFile, json_encode($bilhetes, JSON_PRETTY_PRINT));
    echo json_encode(["ok"=>true]);
    exit;
}

// CONFIRMAR
if ($acao == 'confirmar') {
    $id = $_POST['id'];

    if ($bilhetes[$id]['status'] != "reservado") exit;

    $bilhetes[$id]['status'] = "confirmado";

    file_put_contents($bilhetesFile, json_encode($bilhetes, JSON_PRETTY_PRINT));
    echo json_encode(["ok"=>true]);
    exit;
}

// RESETAR TODOS
if ($acao == "resetarTodos") {

    foreach ($bilhetes as $id => $b) {
        $bilhetes[$id]['comprador'] = null;
        $bilhetes[$id]['status'] = "disponivel";
    }

    file_put_contents($bilhetesFile, json_encode($bilhetes, JSON_PRETTY_PRINT));

    echo json_encode(["ok"=>true]);
    exit;
}

// RESETAR UM
if ($acao == "resetarUm") {

    $id = $_POST['id'];

    $bilhetes[$id]['comprador'] = null;
    $bilhetes[$id]['status'] = "disponivel";

    file_put_contents($bilhetesFile, json_encode($bilhetes, JSON_PRETTY_PRINT));

    echo json_encode(["ok"=>true]);
    exit;
}

// RESULTADO
if ($acao == "resultado") {
    echo file_get_contents($resultadoFile);
    exit;
}

// SORTEAR (4 números)
if ($acao == "sortear") {

    $n = [
        intval($_POST['n1']),
        intval($_POST['n2']),
        intval($_POST['n3']),
        intval($_POST['n4'])
    ];

    $resultado = [
        "numeros"=>$n,
        "vencedor"=>null
    ];

    foreach ($bilhetes as $id => $b) {

        if ($b['status'] != "confirmado") continue;

        for ($i=0; $i<4; $i++) {
            $bloco = array_slice($b['numeros'], $i*4, 4);

            if (count(array_intersect($n, $bloco)) == 4) {
                $resultado['vencedor'] = $id;
                break 2;
            }
        }
    }

    file_put_contents($resultadoFile, json_encode($resultado, JSON_PRETTY_PRINT));

    echo json_encode(["ok"=>true]);
    exit;
}