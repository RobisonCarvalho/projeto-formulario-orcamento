
<?php

use PHPMailer\PHPMailer\PHPMailer;

header('Content-Type: application/json; charset=UTF-8');

// Carregar o PHPMailer
require __DIR__ . '/../vendor/autoload.php';

// Carregar as configurações SMTP
$config = require __DIR__ . '/../config/mail.php';

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

    $totalFiles = count($files['name']);

    if ($totalFiles > $maxFiles) {
        responder(400, 'Pode anexar no máximo 3 arquivos.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);

    for ($i = 0; $i < $totalFiles; $i++) {

        if ($files['error'][$i] === UPLOAD_ERR_NO_FILE) {
            continue;
        }

        if ($files['error'][$i] !== UPLOAD_ERR_OK) {
            responder(400, 'Ocorreu um erro durante o envio de um arquivo.');
        }

        if ($files['size'][$i] > $maxFileSize) {
            responder(400, 'Cada arquivo deve ter no máximo 5 MB.');
        }

        if (!is_uploaded_file($files['tmp_name'][$i])) {
            responder(400, 'Arquivo inválido.');
        }

        $extension = strtolower(
            pathinfo($files['name'][$i], PATHINFO_EXTENSION)
        );

        $mimeType = $finfo->file($files['tmp_name'][$i]);

        if (
            !isset($allowedFiles[$extension]) ||
            $allowedFiles[$extension] !== $mimeType
        ) {
            responder(400, 'Formato de arquivo não permitido.');
        }
    }
}

// INICIAR ENVIO DO E-MAIL

$mail = new PHPMailer(true);

try {

    // Configurações do servidor SMTP
    $mail->isSMTP();
    $mail->Host       = $config['host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $config['username'];
    $mail->Password   = $config['password'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = $config['port'];

    // Configuração do e-mail
    $mail->CharSet = 'UTF-8';

    // Remetente
    $mail->setFrom($config['username'], 'Pedidos de Orçamento');

    // Destinatário
    $mail->addAddress($config['recipient']);

    // Permitir responder diretamente ao cliente
    $mail->addReplyTo($email, $nome);

    // Definir o assunto
    $mail->Subject = 'Novo pedido de orçamento - ' . $servico;

    // Ativar o formato HTML
    $mail->isHTML(true);

    // Preparar dados para exibição segura no HTML
    $nomeHtml = htmlspecialchars($nome, ENT_QUOTES, 'UTF-8');
    $emailHtml = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    $telefoneHtml = htmlspecialchars($telefone, ENT_QUOTES, 'UTF-8');
    $servicoHtml = htmlspecialchars($servico, ENT_QUOTES, 'UTF-8');

    $descricaoHtml = nl2br(
        htmlspecialchars($descricao, ENT_QUOTES, 'UTF-8')
    );

    // Montar o conteúdo HTML
    $mail->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; color: #353524;'>

            <h2 style='color: #A95332;'>Novo pedido de orçamento</h2>

            <p>Um novo pedido foi enviado pelo formulário do site.</p>

            <hr>

            <h3>Dados do cliente</h3>

            <p><strong>Nome:</strong> {$nomeHtml}</p>
            <p><strong>E-mail:</strong> {$emailHtml}</p>
            <p><strong>Telefone:</strong> {$telefoneHtml}</p>
            <p><strong>Serviço:</strong> {$servicoHtml}</p>

            <hr>

            <h3>Descrição do projeto</h3>

            <p>{$descricaoHtml}</p>

            <hr>

            <p style='font-size: 12px; color: #888;'>
                Mensagem enviada através do formulário de orçamento.
            </p>

        </div>
    ";

    // Versão em texto simples
    $mail->AltBody =
        "Novo pedido de orçamento\n\n" .
        "Nome: {$nome}\n" .
        "E-mail: {$email}\n" .
        "Telefone: {$telefone}\n" .
        "Serviço: {$servico}\n\n" .
        "Descrição:\n{$descricao}";

    // ADICIONAR ANEXOS

    if ($files !== null) {

        for ($i = 0; $i < count($files['name']); $i++) {

            if ($files['error'][$i] === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $mail->addAttachment(
                $files['tmp_name'][$i],
                basename($files['name'][$i])
            );
        }
    }

    // ENVIAR O E-MAIL
    $mail->send();

    // Responder ao JavaScript
    responder(200, 'Pedido de orçamento enviado com sucesso!');

} catch (Throwable $e) {

    // Registrar o erro no log do servidor
    error_log('Erro no envio do orçamento: ' . $e->getMessage());

    responder(500, 'Não foi possível enviar o orçamento. Tente novamente.');

}
