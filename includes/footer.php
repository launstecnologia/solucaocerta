

<script src="<?= $url; ?>/assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="<?= $url; ?>/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="<?= $url; ?>/assets/libs/simplebar/dist/simplebar.js"></script>
  <script src="<?= $url; ?>/assets/js/sidebarmenu.js"></script>
  <script src="<?= $url; ?>/assets/js/app.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js" integrity="sha512-pHVGpX7F/27yZ0ISY+VVjyULApbDlD0/X0rgGbTqCE7WFW5MezNTWG/dnhtbBuICzsd0WQPgpE4REBLv+UqChw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

  <script type="text/javascript">

function toggleSubMenu(element) {
  const submenu = element.nextElementSibling;
  const icon = element.querySelector('.toggle-icon');

  if (submenu) {
    // Alterna a visibilidade do submenu
    if (submenu.style.display === "none" || submenu.style.display === "") {
      submenu.style.display = "block"; // Mostra o submenu
      icon.style.transform = "rotate(90deg)"; // Gira o ícone de seta
    } else {
      submenu.style.display = "none"; // Esconde o submenu
      icon.style.transform = "rotate(0deg)"; // Reseta o ícone de seta
    }
  }
}




    $(document).ready(function(){
      $("#cnpj").mask("99.999.999/9999-99");
      $("#cpf").mask("999.999.999-99");
      $("#cpf").mask("999.999.999-99");
      $("#cep").mask("99.999-999");

      var SPMaskBehavior = function (val) {
          return val.replace(/\D/g, '').length === 11 ? '(00) 00000-0000' : '(00) 0000-00009';
      },
      spOptions = {
          onKeyPress: function(val, e, field, options) {
              field.mask(SPMaskBehavior.apply({}, arguments), options);
          }
      };

      $('#telefone1').mask(SPMaskBehavior, spOptions);
      $('#telefone2').mask(SPMaskBehavior, spOptions);

      // Máscara para valor monetário
      $('#valor').mask("###0.00", {reverse: true});
    });

    document.addEventListener("DOMContentLoaded", function () {
    // Seleciona o botão e a sidebar
    const sidebarToggle = document.getElementById("sidebarToggle");
    const leftSidebar = document.querySelector(".left-sidebar");
    const mainWrapper = document.getElementById("main-wrapper");

    // Evento de clique no botão
    sidebarToggle.addEventListener("click", function () {
        if (leftSidebar) {
            // Adiciona ou remove a classe 'show-sidebar' no main-wrapper
            mainWrapper.classList.toggle("show-sidebar");

            // Verifica se a sidebar está visível e aplica estilos diretamente
            if (mainWrapper.classList.contains("show-sidebar")) {
                leftSidebar.style.transform = "translateX(0)";
            } else {
                leftSidebar.style.transform = "translateX(-100%)";
            }
        }
    });
});

  </script>


</body>

</html>