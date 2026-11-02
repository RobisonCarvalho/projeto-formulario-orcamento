<?php

header('Content-Type: application/json; charset=UTF-8');

// Função para responder ao JavaScript
function responder($status, $mensagem) {
    http_response_code($status);

    echo json_encode([
        'success' => $status >= 200 && $status < 300,
        'message' => $mensagem
    ]);

    exit;
}

// Aceitar somente requisições POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(405, 'Método de requisição não permitido.');
}

// Confirmar que os dados chegaram
if (empty($_POST)) {
    responder(400, 'Nenhum dado foi recebido.');
}

// Receber e limpar os campos
$nome = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefone = trim($_POST['phone'] ?? '');
$servico = trim($_POST['service'] ?? '');
$descricao = trim($_POST['description'] ?? '');

// Validar campos obrigatórios
if (
    $nome === '' ||
    $email === '' ||
    $telefone === '' ||
    $servico === '' ||
    $descricao === ''
) {
    responder(400, 'Preencha todos os campos obrigatórios.');
}

// Validar nome
if (mb_strlen($nome) < 3 || mb_strlen($nome) > 100) {
    responder(400, 'O nome deve ter entre 3 e 100 caracteres.');
}

// Validar email
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
    responder(400, 'Introduza um email válido.');
}

// Validar telefone
if (
    strlen($telefone) < 6 ||
    strlen($telefone) > 25 ||
    !preg_match('/^[0-9+().\s-]+$/', $telefone)
) {
    responder(400, 'Introduza um telefone válido.');
}

// Validar serviço
$servicosPermitidos = [
    'website',
    'landing-page',
    'redesign',
    'other'
];

if (!in_array($servico, $servicosPermitidos, true)) {
    responder(400, 'Selecione um serviço válido.');
}

// Validar descrição
if (mb_strlen($descricao) < 20 || mb_strlen($descricao) > 5000) {
    responder(400, 'A descrição deve ter entre 20 e 5000 caracteres.');
}

// VALIDAR ANEXOS

$maxFiles = 3;
$maxFileSize = 5 * 1024 * 1024;

$allowedFiles = [
    'pdf'  => 'application/pdf',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png'
];

// Verificar se existem arquivos enviados
$files = $_FILES['attachments'] ?? null;

if ($files !== null) {

    // Verificar quantidade de arquivos
    $totalFiles = count($files['name']);

    if ($totalFiles > $maxFiles) {
        responder(400, 'Pode anexar no máximo 3 arquivos.');
    }

    // Verificar cada arquivo
    $finfo = new finfo(FILEINFO_MIME_TYPE);

    for ($i = 0; $i < $totalFiles; $i++) {

        // Ignorar campo vazio
        if ($files['error'][$i] === UPLOAD_ERR_NO_FILE) {
            continue;
        }

        // Verificar erros de upload
        if ($files['error'][$i] !== UPLOAD_ERR_OK) {
            responder(400, 'Ocorreu um erro durante o envio de um arquivo.');
        }

        // Verificar tamanho
        if ($files['size'][$i] > $maxFileSize) {
            responder(400, 'Cada arquivo deve ter no máximo 5 MB.');
        }

        // Verificar se o arquivo foi realmente enviado
        if (!is_uploaded_file($files['tmp_name'][$i])) {
            responder(400, 'Arquivo inválido.');
        }

        // Obter extensão
        $extension = strtolower(
            pathinfo($files['name'][$i], PATHINFO_EXTENSION)
        );

        // Identificar tipo real do arquivo
        $mimeType = $finfo->file($files['tmp_name'][$i]);

        // Conferir extensão e conteúdo
        if (
            !isset($allowedFiles[$extension]) ||
            $allowedFiles[$extension] !== $mimeType
        ) {
            responder(400, 'Formato de arquivo não permitido.');
        }
    }
}

// Se chegou aqui, todos os campos são válidos
responder(200, 'Dados recebidos e validados com sucesso!');

