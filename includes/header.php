<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sistema - Solução Certa</title>
  <link rel="shortcut icon" type="image/png" href="<?= $url; ?>/assets/images/logos/favicon.png" />
  <link rel="stylesheet" href="<?= $url; ?>/assets/css/styles.min.css" />
  <style>
    /* Estilo do submenu */
    /* Estilo do submenu */
    .submenu {
      list-style: none;
      margin: 0;
      padding: 0 0 0 20px;
      /* Adiciona espaçamento para dentro */
    }

    .submenu li {
      margin: 10px 0;
      /* Adiciona espaçamento entre itens */
    }

    .submenu li a {
      text-decoration: none;
      color: #333;
      /* Cor do texto */
      display: flex;
      align-items: center;
      /* Alinha o ícone com o texto */
      gap: 10px;
      /* Espaço entre ícone e texto */
      font-size: 14px;
      /* Ajuste do tamanho da fonte */
    }

    .submenu li a:hover {
      color: #007bff;
      /* Cor ao passar o mouse */
    }

    /* Ícone de seta no link principal */
    .toggle-icon {
      margin-left: auto;
      /* Move o ícone para a direita */
      transition: transform 0.3s ease;
      /* Animação de rotação */
    }

    /* Ícone do submenu */
    .submenu-icon {
      font-size: 10px;
      /* Tamanho menor para os ícones do submenu */
      color: #007bff;
    }


    /* Estilo para mobile */
@media (max-width: 1199px) {
    #main-wrapper .left-sidebar {
        transform: translateX(-100%);
        /* Esconde inicialmente a sidebar */
        transition: transform 0.3s ease;
        position: fixed;
        top: 0;
        bottom: 0;
        z-index: 1050;
        background-color: #fff;
        width: 250px;
    }

    #main-wrapper.show-sidebar .left-sidebar {
        transform: translateX(0);
        /* Mostra a sidebar */
        transition: transform 0.3s ease;
    }

    /* Ajusta o conteúdo principal quando a sidebar está visível */
    #main-wrapper.show-sidebar .body-wrapper {
        margin-left: 250px;
    }
}


    
    
  </style>
</head>

<body>
  <!--  Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <!-- Sidebar Start -->
    <aside class="left-sidebar">
      <!-- Sidebar scroll-->
      <div>
        <div class="brand-logo d-flex align-items-center justify-content-between">
          <a href="./index.html" class="text-nowrap logo-img">
            <img src="<?= $url; ?>/assets/images/logos/logo.png" alt="" width="200" />
          </a>
        </div>
        <!-- Sidebar navigation-->
        <nav class="sidebar-nav scroll-sidebar" data-simplebar="">
          <ul id="sidebarnav">
            <li class="nav-small-cap">
              <i class="ti ti-dots nav-small-cap-icon fs-6"></i>
              <span class="hide-menu">Principal</span>
            </li>


            <li class="sidebar-item">
              <a class="sidebar-link" href="<?= $url; ?>/dashboard.php" aria-expanded="false">
                <span>
                  <iconify-icon icon="solar:home-smile-bold-duotone" class="fs-6"></iconify-icon>
                </span>
                <span class="hide-menu">Dashboard</span>
              </a>
            </li>

            <li class="sidebar-item">
              <a class="sidebar-link" href="<?= $url; ?>/clientes" aria-expanded="false">
                <span>
                  <iconify-icon icon="solar:layers-minimalistic-bold-duotone" class="fs-6"></iconify-icon>
                </span>
                <span class="hide-menu">Clientes</span>
              </a>
            </li>

            <li class="sidebar-item">
              <a class="sidebar-link" href="<?= $url; ?>/kanban" aria-expanded="false">
                <span>
                  <iconify-icon icon="solar:users-group-rounded-bold-duotone" class="fs-6"></iconify-icon>
                </span>
                <span class="hide-menu">CRM</span>
              </a>
            </li>

            <li class="sidebar-item">
              <a class="sidebar-link" href="<?= $url; ?>/representantes" aria-expanded="false">
                <span>
                  <iconify-icon icon="solar:danger-circle-bold-duotone" class="fs-6"></iconify-icon>
                </span>
                <span class="hide-menu">Representante</span>
              </a>
            </li>


            <li class="sidebar-item">
              <a class="sidebar-link" href="javascript:void(0);" aria-expanded="false" onclick="toggleSubMenu(this)">
                <span>
                  <iconify-icon icon="mdi:wallet" class="fs-6"></iconify-icon>

                </span>
                <span class="hide-menu">Comissão Geral</span>
                <iconify-icon icon="akar-icons:chevron-right" class="toggle-icon"></iconify-icon>
              </a>
              <!-- Submenu -->
              <ul class="submenu" style="display: none; padding-left: 20px;">
                <li>
                  <a href="<?= $url; ?>/comissao/comissao_pagseguro_paytime.php">
                    <iconify-icon icon="akar-icons:circle" class="submenu-icon"></iconify-icon> PagBank
                  </a>
                </li>
                <li>
                  <a href="<?= $url; ?>/comissao/bcard.php">
                    <iconify-icon icon="akar-icons:circle" class="submenu-icon"></iconify-icon> Brasil Card
                  </a>
                </li>
                <li>
                  <a href="<?= $url; ?>/comissao/adesao.php">
                    <iconify-icon icon="akar-icons:circle" class="submenu-icon"></iconify-icon> Adesão
                  </a>
                </li>
              </ul>
            </li>


            <li class="sidebar-item">
              <a class="sidebar-link" href="javascript:void(0);" aria-expanded="false" onclick="toggleSubMenu(this)">
                <span>
                  <iconify-icon icon="mdi:wallet" class="fs-6"></iconify-icon>

                </span>
                <span class="hide-menu">Comissão Rep.</span>
                <iconify-icon icon="akar-icons:chevron-right" class="toggle-icon"></iconify-icon>
              </a>
              <!-- Submenu -->
              <ul class="submenu" style="display: none; padding-left: 20px;">
              <li>
                  <a href="<?= $url; ?>/comissao/comissao_pagseguro_rep.php">
                    <iconify-icon icon="akar-icons:circle" class="submenu-icon"></iconify-icon> PagBank
                  </a>
                </li>
                <li>
                  <a href="<?= $url; ?>/comissao/comissao_bcard_rep.php">
                    <iconify-icon icon="akar-icons:circle" class="submenu-icon"></iconify-icon> Brasil Card
                  </a>
                </li>
                <li>
                  <a href="<?= $url; ?>/comissao/comissao_soufacil_rep.php">
                    <iconify-icon icon="akar-icons:circle" class="submenu-icon"></iconify-icon> Sou Fácil
                  </a>
                </li>
              </ul>
            </li>



            

            <li class="sidebar-item">
              <a class="sidebar-link" href="javascript:void(0);" aria-expanded="false" onclick="toggleSubMenu(this)">
                <span>
                  <iconify-icon icon="mdi:wallet" class="fs-6"></iconify-icon>

                </span>
                <span class="hide-menu">Faturamento</span>
                <iconify-icon icon="akar-icons:chevron-right" class="toggle-icon"></iconify-icon>
              </a>
              <!-- Submenu -->
              <ul class="submenu" style="display: none; padding-left: 20px;">
                <li>
                  <a href="<?= $url; ?>/faturamento/brasil_card.php">
                    <iconify-icon icon="akar-icons:circle" class="submenu-icon"></iconify-icon> Brasil Card
                  </a>
                </li>
                <li>
                  <a href="<?= $url; ?>/faturamento/soufacil.php">
                    <iconify-icon icon="akar-icons:circle" class="submenu-icon"></iconify-icon> Sou Fácil
                  </a>
                </li>
              </ul>
            </li>


            <li class="sidebar-item">
              <a class="sidebar-link" href="<?= $url; ?>/ticket" aria-expanded="false">
                <span>
                  <iconify-icon icon="solar:bookmark-square-minimalistic-bold-duotone" class="fs-6"></iconify-icon>
                </span>
                <span class="hide-menu">Suporte</span>
              </a>
            </li>

            <li class="sidebar-item">
              <a class="sidebar-link" href="<?= $url; ?>/usuarios" aria-expanded="false">
                <span>
                  <iconify-icon icon="solar:file-text-bold-duotone" class="fs-6"></iconify-icon>
                </span>
                <span class="hide-menu">Usuários</span>
              </a>
            </li>

            <li class="sidebar-item">
              <a class="sidebar-link" href="<?= $url; ?>/login/logout.php" aria-expanded="false">
                <span>
                  <iconify-icon icon="solar:text-field-focus-bold-duotone" class="fs-6"></iconify-icon>
                </span>
                <span class="hide-menu">Sair</span>
              </a>
            </li>

        </nav>
        <!-- End Sidebar navigation -->
      </div>
      <!-- End Sidebar scroll-->
    </aside>
    <!--  Sidebar End -->
    <!--  Main wrapper -->
    <div class="body-wrapper">
      <!--  Header Start -->
      <header class="app-header">
        <nav class="navbar navbar-expand-lg navbar-light">

        <!-- Botão para abrir/fechar o menu lateral -->
        <button id="sidebarToggle" class="btn btn-primary d-xl-none">
          <i class="ti ti-menu-2"></i>
        </button>

          <div class="navbar-collapse justify-content-end px-0" id="navbarNav">
            <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-end">
              <li class="nav-item dropdown">
                <a class="nav-link nav-icon-hover" href="javascript:void(0)" id="drop2" data-bs-toggle="dropdown"
                  aria-expanded="false">
                  <img src="../assets/images/profile/user-1.jpg" alt="" width="35" height="35" class="rounded-circle">
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop2">
                  <div class="message-body">
                    <a href="<?= $url; ?>/login/logout.php" class="btn btn-outline-primary mx-3 mt-2 d-block">Sair</a>
                  </div>
                </div>
              </li>
            </ul>
          </div>
        </nav>
      </header>
      <!--  Header End -->