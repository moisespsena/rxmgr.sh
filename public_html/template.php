<?php
function pre_html($title, $topbar=true, $help=true) {
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
<?php if($topbar) { ?>
    <div id="top-header">
    <?php if($title) { ?><h1><?php echo $title; ?></h1><?php } ?>
    </div>
<?php } ?>
<div id="panel"></div>
<div id="main">
    <?php if($help) { ?>
    <div><a href="#" class="toggle-help">Ajuda</a></div>
    <div id="help" style="display:none">
        <a href="#" class="close toggle-help">Fechar</a>
        <h2>Como acessar</h2>
        <h3>Do Linux</h3>
        <ol>
            <li>Instale o aplicativo chamado <b>vinagre</b>;</li>
            <li>Clique no botão <b>conectar</b>;</li>
            <li>Em <b>protocolo</b>, escola <b>VNC</b>;</li>
            <li>em <b>Máquina</b>, coloque o IP (ou dominio) e PORTA, conforme mostrado na página inicial, seguindo o modelo (IP:PORTA, exemplo: <b>192.168.1.5:5901</b>);</li>
            <li>Escola a <b>Profundidade da cor</b> para definir a qualidade da conexão. Quando melhor, mais lenta;</li>
            <li>Clique em <b>Conectar</b>.</li>
            <li>Ao solicitar a senha, entre com a senha do usuário correspondente.</li>
        </ol>
        <h3>Do Windows</h3>
        <ol>
            <li>Acesse a <a href="http://www.tightvnc.com/download.php">página de download</a> do <b>TightVNC</b>, faça o download do <b>TightVNC</b> e do <b>DFMirage driver</b>;</li>
            <li>Instale os executáveis.</li>
            <li>Acesse o menu inciar e procure por <b>TightVNC</b>, clique em <b>Tight VNV Viewer</b>;</li>
            <li>Em <b>Remote Host</b>, coloque o IP (ou dominio) e PORTA, conforme mostrado na página inicial, seguindo o modelo (IP:PORTA, exemplo: <b>192.168.1.5:5901</b>);</li>
            <li>Clique em <b>connect</b>;</li>
            <li>Ao solicitar a senha, entre com a senha do usuário correspondente.</li>
        </ol> 
        <h2>Envio e Download de Arquivos</h2>
        <h3>Servidor de FTP</h3>
        <p>Por padrao, o servidor de FTP está rodando em <code><?php echo $_SERVER['HTTP_HOST']; ?></code>na porta <code>21</code>.</p>
        <p><b>Login</b> e <b>senha</b> são os mesmos da Área de Trabalho remota</p>
        <h3>Acesso</h3>
        <h4>Do Linux</h4>
        <p>Instale o pacote <b>filezilla</b>: <code>sudo apt-get install filezilla</code></p>
        <h4>Do Windows</h4>
        <p>Instale o <a href="https://filezilla-project.org/download.php?show_all=1" title="Ir para a pagina de Download do Filezilla"><b>filezilla</b></a>.</p>
    </div>
<?php } ?>
<?php
}

function end_html() {
?>
</div>
<div class="txt-center" id="footer" style="border-top: 1px solid #999">
Desenvolvido por &copy;Moisés Paes Sena - <a href="http://moisespsena.com">moisespsena.com</a>
</div>
</body>
</html>
<?php
}

