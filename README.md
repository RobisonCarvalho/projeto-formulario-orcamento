# Projeto — Formulário de Orçamento

Formulário profissional para solicitação de orçamentos de serviços de desenvolvimento web, construído com HTML, CSS, JavaScript e PHP.

O projeto permite que potenciais clientes enviem os seus dados, descrevam as necessidades do projeto e anexem arquivos de referência. As informações são validadas no front-end e no back-end e enviadas por e-mail através de SMTP com PHPMailer.

## Funcionalidades

- Formulário de solicitação de orçamento.
- Campos para nome, e-mail, telefone, serviço e descrição do projeto.
- Seleção do tipo de serviço pretendido.
- Upload de até 3 arquivos por solicitação.
- Suporte para arquivos PDF, JPG, JPEG e PNG.
- Limite de 5 MB por arquivo.
- Validação de dados no JavaScript e no PHP.
- Verificação de extensão, MIME type e tamanho dos arquivos.
- Envio de e-mails através de SMTP com PHPMailer.
- E-mail em HTML com versão alternativa em texto simples.
- Mensagens de sucesso e erro sem recarregar a página.
- Layout responsivo para dispositivos móveis e computadores.

## Tecnologias utilizadas

- HTML5
- CSS3
- JavaScript
- PHP 8+
- PHPMailer
- Composer
- SMTP

## Estrutura do projeto

```text
projeto-formulario-orcamento/
├── config/
│   ├── .htaccess
│   ├── mail.example.php
│   └── mail.php
├── css/
│   └── style.css
├── js/
│   └── script.js
├── php/
│   └── send.php
├── vendor/
├── .gitignore
├── composer.json
├── composer.lock
└── index.html
```

## Instalação

### 1. Clonar o repositório

```bash
git clone URL_DO_REPOSITORIO
```

### 2. Entrar na pasta

```bash
cd projeto-formulario-orcamento
```

### 3. Instalar as dependências

```bash
composer install
```

### 4. Configurar o SMTP

Crie ou configure o arquivo `config/mail.php` com os dados da sua conta de e-mail.

Exemplo de configuração:

```php
<?php

return [
    'host' => 'smtp.gmail.com',
    'username' => 'SEU_EMAIL',
    'password' => 'SUA_SENHA_DE_APLICATIVO',
    'recipient' => 'EMAIL_DESTINO',
    'port' => 587
];
```

**Importante:** nunca publique o arquivo `config/mail.php` com credenciais reais. Utilize o `mail.example.php` como referência e mantenha o arquivo privado através do `.gitignore`.

### 5. Executar localmente

Configure o projeto num servidor com suporte a PHP, como XAMPP, e acesse pelo navegador:

```text
http://localhost/projeto-formulario-orcamento/
```

## Segurança

O projeto inclui algumas medidas básicas de segurança:

- Validação dos campos no servidor.
- Verificação do formato e tamanho dos arquivos.
- Conferência do MIME type através do PHP Fileinfo.
- Uso de `htmlspecialchars()` para evitar a interpretação de conteúdo enviado pelo utilizador como HTML.
- Proteção do diretório de configurações através de `.htaccess`.
- Credenciais SMTP excluídas do repositório Git.

## Objetivo do projeto

Este projeto faz parte de uma sequência de estudos e desenvolvimento prático, com o objetivo de aprofundar conhecimentos em desenvolvimento web, integração entre front-end e back-end, processamento de formulários, envio de e-mails e segurança básica em aplicações PHP.

## Autor

**Robison Carvalho**

Desenvolvimento Web | HTML | CSS | JavaScript | PHP

[GitHub](https://github.com/RobisonCarvalho)

[Portfólio](https://roy.freedev.app)
