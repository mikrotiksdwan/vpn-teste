<!-- Trocar Senha (Após Login) -->
<?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']): ?>
<div class="text-center mb-4">
    <h5>Olá, <?php echo $_SESSION['user_email']; ?></h5>
    <p class="text-muted">Digite sua nova senha abaixo</p>
</div>
<form method="POST">
    <div class="mb-3">
        <label class="form-label">Nova Senha</label>
        <input type="password" class="form-control" name="new_password" required 
               placeholder="Mínimo 8 caracteres" minlength="8">
    </div>
    <div class="mb-3">
        <label class="form-label">Confirmar Nova Senha</label>
        <input type="password" class="form-control" name="confirm_password" required 
               placeholder="Digite a senha novamente" minlength="8">
    </div>
    <button type="submit" name="change_password" class="btn btn-primary w-100 mb-3">
        <i class="bi bi-key"></i> Alterar Senha
    </button>
    <div class="text-center">
        <a href="?logout=true" class="text-decoration-none text-danger">
            <i class="bi bi-box-arrow-right"></i> Sair
        </a>
    </div>
</form>
<?php endif; ?>
