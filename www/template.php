<?php
function pre_html($title) {
?>
<!doctype html>

<html lang="pt_BR">
<head>
  <meta charset="utf-8">

  <title><?php echo $title ?></title>
  <meta name="description" content="The HTML5 Herald">
  <meta name="author" content="SitePoint">

  <link rel="stylesheet" href="styles.css">
  <script src="jquery-2.1.3.min.js"></script>
  <script src="js.js"></script>

  <!--[if lt IE 9]>
  <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
  <![endif]-->
  <script type="text/template" id="tpl_create_display">
      <form>
      <div id="panel-head">
          <h2>Criar nova área de trabalho remota para <i>"{user}"</i></h2>
      </div>
      <div id="panel-content">
          <div class="txt-center"><label>Senha do usuário <b>{user}</b>:<br /><input type="password" size="40" minength="6" class="txt-center" name="password"/></label></div>
          <fieldset>
              <legend>Tamanho da Tela</legend>
              <div class="left txt-center" style="width:33.3%">
                  <label>Largura:<br /><input type="text" size="6" name="width"/></label>
              </div>
              <div class="left txt-center" style="width:33.3%">
                  <label>Altura:<br /><input type="text" size="6" name="height"/></label>
              </div>
              <div class="left txt-center" style="width:33.3%">
                  <br /><a href="javascript:void(0)" class="set-screen-size">tela inteira</a>
              </div>
              <div style="clear:both"></div>
          </fieldset>
      </div>
      <div id="panel-controls">
          <div class="left"><a href="#" class="cancel">Cancelar</a></div>
          <div class="right"><button class="ok">Criar</button></div>
          <div style="clear:both"></div>
      </div>
      </form>
  </script>

  <script type="text/template" id="tpl_close_display">
      <form>
      <div id="panel-head">
          <h2>ECERRAR Área de Trabalho '{display}'</h2>
      </div>
      <div id="panel-content">
          <p class="txt-center"><label>Senha do usuário <b>{user}</b>:<br /><input type="password" size="20" name="password" class="txt-center"/></label></p>
      </div>
      <div id="panel-controls">
          <div class="left"><a href="#" class="cancel">Cancelar</a></div>
          <div class="right"><button class="ok">ENCERRAR</button></div>
          <div style="clear:both"></div>
      </div>
      </form>
  </script>

</head>

<body>
<?php if($title) { ?><h1><?php echo $title; ?></h1><?php } ?>
<div id="main">
<?php
}

function end_html() {
?>
</div>
<div id="panel">
</div>
<div class="txt-center" id="footer" style="border-top: 1px solid #999">
Desenvolvido por &copy;Moisés Paes Sena - <a href="http://moisespsena.com">moisespsena.com</a>
</div>
</body>
</html>
<?php
}
