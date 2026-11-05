// Selecionar o formulário
const budgetForm = document.getElementById('budgetForm');

// Selecionar a área de mensagens
const formMessage = document.getElementById('formMessage');

if (budgetForm && formMessage) {

    console.log('Formulário de orçamento carregado!');

    budgetForm.addEventListener('submit', function(event) {

        // Impedir o envio tradicional
        event.preventDefault();

        // Capturar os dados
        const formData = new FormData(budgetForm);

        const name = (formData.get('name') || '').trim();
        const email = (formData.get('email') || '').trim();
        const description = (formData.get('description') || '').trim();

        // Limpar mensagem anterior
        formMessage.textContent = '';
        formMessage.className = 'form-message';

        // Validar nome
        if (name.length < 3) {
            showMessage('O nome deve ter pelo menos 3 caracteres.', 'error');
            return;
        }

        // Validar email
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!emailPattern.test(email)) {
            showMessage('Introduza um email válido.', 'error');
            return;
        }

        // Validar descrição
        if (description.length < 20) {
            showMessage('Descreva o seu projeto com pelo menos 20 caracteres.', 'error');
            return;
        }

        // VALIDAR ANEXOS

        const files = formData.getAll('attachments[]')
            .filter(file => file.name !== '');

        const allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];

        const maxFileSize = 5 * 1024 * 1024;
        const maxFiles = 3;

        // Verificar quantidade de arquivos
        if (files.length > maxFiles) {
            showMessage('Pode anexar no máximo 3 arquivos.', 'error');
            return;
        }

        // Verificar cada arquivo
        for (const file of files) {

            // Obter extensão
            const extension = file.name.split('.').pop().toLowerCase();

            // Verificar formato
            if (!allowedExtensions.includes(extension)) {
                showMessage('Formato não permitido: ' + file.name, 'error');
                return;
            }

            // Verificar tamanho
            if (file.size > maxFileSize) {
                showMessage('O arquivo ' + file.name + ' ultrapassa 5 MB.', 'error');
                return;
            }

        }

        // Se chegou aqui, passou nas validações
        const submitButton = budgetForm.querySelector('button[type="submit"]');

        if (!submitButton) {
            showMessage('Botão de envio não encontrado.', 'error');
            return;
        }

        submitButton.disabled = true;
        submitButton.textContent = 'Enviando...';

        fetch('php/send.php', {
        method: 'POST',
        body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro HTTP: ' + response.status);
            }

            return response.json();
        })
        .then(data => {
            if (data.success) {
                showMessage(data.message, 'success');
                budgetForm.reset();
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            showMessage('Não foi possível enviar os dados. Tente novamente.', 'error');
            console.error('Erro:', error);
        })
        .finally(() => {
            submitButton.disabled = false;
            submitButton.textContent = 'Solicitar orçamento';
        });

    });

}

// Função para apresentar mensagens
function showMessage(message, type) {

    formMessage.textContent = message;
    formMessage.classList.add(type);

}