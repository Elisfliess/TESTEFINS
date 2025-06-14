<?php
session_start();
require_once('conexao_azure.php');

if (!isset($_SESSION['admin_logado'])) {
    header("Location: login.php");
    exit();
}

$mensagem = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idcategoria = $_POST['idcategoria'];
    $nome = trim($_POST['nome']);  // Remove espaços antes e depois

    if (empty($nome)) {
        $mensagem = "<p class='mensagem erro'>O nome da subcategoria é obrigatório.</p>";
    } else {
        // Verifica duplicidade na mesma categoria
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM subcategoria WHERE LOWER(nome) = LOWER(:nome) AND id_categoria = :idcategoria");
        $stmt->bindParam(':nome', $nome, PDO::PARAM_STR);
        $stmt->bindParam(':idcategoria', $idcategoria, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->fetchColumn() > 0) {
            $mensagem = "<p class='mensagem erro'>Esta Subcategoria já está cadastrada nesta categoria.</p>";
        } else {
            try {
                $sql = "INSERT INTO subcategoria (id_categoria, nome) VALUES (:idcategoria, :nome);";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':idcategoria', $idcategoria, PDO::PARAM_INT);
                $stmt->bindParam(':nome', $nome, PDO::PARAM_STR);
                $stmt->execute();

                $sub_id = htmlspecialchars($pdo->lastInsertId());
                $mensagem = "<p class='mensagem sucesso'>Subcategoria cadastrada com sucesso! ID: $sub_id</p>";
            } catch (PDOException $e) {
                $mensagem = "<p class='mensagem erro'>Erro ao cadastrar Subcategoria: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Subcategoria</title>
    <link rel="stylesheet" href="../css/menu.css">
      <link rel="stylesheet" href="../css/cadastrar_subcategorias.css">
   
  
</head>
<body>


 <!-- MENU HAMBURGUER -->
    <button class="menu-btn" aria-label="Abrir menu" aria-expanded="false">&#9776;</button>
    
    <!-- Menu suspenso -->
    <div class="hamburguer">
        <img class="logo" src="../img/Logo.png" alt="Logo">
        <nav class="nav">
            <ul>
                <li class="category"><a href="#">ADMINISTRADOR</a>
                    <ul class="submenu">
                        <li><a href="./listar_administrador.php">LISTAR</a></li>
                        <li><a href="./cadastrar_administrador.php">CADASTRAR</a></li>
                    </ul>
                </li>
                <li class="category"><a href="#">CATEGORIA</a>
                    <ul class="submenu">
                        <li><a href="listar_categorias.php">LISTAR</a></li>
                        <li><a href="./cadastrar_categorias.php">CADASTRAR</a></li>
                    </ul>
                </li>
                <li class="category"><a href="#">FORNECEDOR</a>
                    <ul class="submenu">
                        <li><a href="listar_fornecedores.php">LISTAR</a></li>
                        <li><a href="./cadastrar_fornecedores.php">CADASTRAR</a></li>
                    </ul>
                </li>
                <li class="category"><a href="#">PRODUTO</a>
                    <ul class="submenu">
                        <li><a href="listar_produtos.php">LISTAR</a></li>
                        <li><a href="./cadastrar_produtos.php">CADASTRAR</a></li>
                    </ul>
                </li>
                <li class="category"><a href="#">SUBCATEGORIA</a>
                    <ul class="submenu">
                        <li><a href="listar_subcategorias.php">LISTAR</a></li>
                        <li><a href="./cadastrar_subcategorias.php">CADASTRAR</a></li>
                    </ul>
                </li>
            </ul>
        </nav>
    </div>

    <!-- JavaScript para ativação do menu -->
    <script>
document.addEventListener("DOMContentLoaded", () => {
    const menuBtn = document.querySelector('.menu-btn');
    const hamburguer = document.querySelector('.hamburguer');
    const categories = document.querySelectorAll(".category");

    // Alterna o menu hambúrguer
    menuBtn.addEventListener("click", (event) => {
        hamburguer.classList.toggle("active");
        event.stopPropagation();

        const isExpanded = hamburguer.classList.contains("active");
        menuBtn.setAttribute("aria-expanded", isExpanded);
        menuBtn.innerHTML = isExpanded ? "✖" : "&#9776;";
    });

    // Submenu por categoria
    categories.forEach(category => {
        category.addEventListener("click", (event) => {
            event.stopPropagation();

            const submenu = category.querySelector(".submenu");
            const isActive = category.classList.contains("active");

            // Fecha todos
            categories.forEach(cat => {
                cat.classList.remove("active");
                const sm = cat.querySelector(".submenu");
                if (sm) {
                    sm.style.maxHeight = "0";
                    sm.style.opacity = "0";
                }
            });

            // Se não estava ativa, abre essa
            if (!isActive && submenu) {
                category.classList.add("active");
                submenu.style.maxHeight = "500px";
                submenu.style.opacity = "1";
            }
        });
    });

    // Fecha menu e submenus ao clicar fora
    document.addEventListener("click", (event) => {
        if (!hamburguer.contains(event.target) && !menuBtn.contains(event.target)) {
            hamburguer.classList.remove("active");
            menuBtn.setAttribute("aria-expanded", "false");
            menuBtn.innerHTML = "&#9776;";

            // Fecha todos submenus
            categories.forEach(category => {
                const submenu = category.querySelector(".submenu");
                if (submenu) {
                    submenu.style.maxHeight = "0";
                    submenu.style.opacity = "0";
                    category.classList.remove("active");
                }
            });
        }
    });
});
</script>

    </script>

    <!-- Fim menu Hamburguer -->


<div class="container">
    <h2>Cadastrar Subcategoria</h2>

    <?php if (!empty($mensagem)) echo $mensagem; ?>

    <form action="" method="post" enctype="multipart/form-data">
        <label for="idcategoria">Categoria:</label>
        <select name="idcategoria" id="idcategoria" required>
            <option value="">Selecione a categoria</option>
            <?php
            $stmt = $pdo->query("SELECT id_categoria, nome FROM categoria");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<option value='{$row['id_categoria']}'>{$row['nome']}</option>";
            }
            ?>
        </select>

        <label for="nome">Nome da Subcategoria:</label>
        <input type="text" name="nome" id="nome" required>

        <button type="submit">Cadastrar Subcategoria</button>
    </form>
    <div class="links">
        <p><a href="painel_admin.php">Voltar ao Painel do Administrador</a></p>
        <p><a href="listar_subcategorias.php">Listar Subcategorias</a></p>
     </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const nomeInput = document.getElementById("nome");
        nomeInput.addEventListener("input", function () {
            this.value = this.value.replace(/[^a-zA-ZÀ-ÿ\s]/g, '');
        });
    });
</script>
</body>
</html>
