<?php
session_start();
if (!isset($_SESSION['usuario'])) {
header("Location: ../../index.php");
}
if ($_SESSION['id_finca_usuario'] == '0') {
session_destroy();
header("Location: ../../index.php");
}
include '../../conexion/conexion.php';
$conexion = conectarServidor();
?>

<div class="right_col" role="main" style="min-height: auto;">
  <div class="">
    <div class="col-md-12 col-sm-12 col-xs-12">
      <div class="x_panel">
        <div class="x_title">
          <h2>Transportes <small></small></h2>
          <div class="clearfix"></div>
        </div>
        <div class="x_content">
          
          <form class="form-horizontal" role="form" onsubmit="event.preventDefault();"> 
            <div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Razón Social</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" class="form-control" autocomplete="off" id="alta_rs" aria-describedby="basic-addon1" required autofocus="">
                  </div>
                </div>
                <div class="form-group form-group-sm">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Observación</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" class="form-control" autocomplete="off" id="alta_obs" aria-describedby="basic-addon1">
                  </div>
                </div>
              </div> <!-- col1 -->
            </div> <!-- row1 -->
            
            <div class="ln_solid"></div>
            
            <div class="form-group">
              <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3" id="div_mensaje_general">
                 <button class="btn btn-default" id="boton_limpiar" type="reset">Limpiar</button>
                 <button type="submit" id='boton_guardar' class="btn btn-primary">Guardar</button>
              </div>
            </div>

          </form>

            <div class="ln_solid"></div>

            <div class="row">  
              <div class="col-md-12 col-sm-12 col-xs-12">
                
                <table id="datatable-responsive" class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
                  <thead>
                    <tr>
                      <th>Razón Social</th>
                      <th>Observación</th>
                      </tr>
                  </thead>
                  <tbody>
                   
                        <?php
                        
                        $sqltransporte = "SELECT
                                    tb_transporte.razon_social as razon_s,
                                    tb_transporte.obs as obs
                                    FROM
                                    tb_transporte
                                    ORDER BY
                                    tb_transporte.razon_social ASC
                                    ";
                        $rstransporte = mysqli_query($conexion, $sqltransporte);
                        
                        $cantidad =  mysqli_num_rows($rstransporte);

                        if ($cantidad > 0) { // si existen transporte con de esa transporte se muestran, de lo contrario queda en blanco  
                       
                        while ($datos = mysqli_fetch_array($rstransporte)){
                        $transporte=utf8_encode($datos['razon_s']);
                        $obs=utf8_encode($datos['obs']);
                        
                        echo '

                        <tr class="even pointer">
                          <td>'.$transporte.'</td>
                          <td>'.$obs.'</td>
                        </tr>
                        ';
                    
                        }   
                        }
                        ?>
                  </tbody>
                </table> 
                
              </div> <!-- col2 -->
            </div> <!-- row2 -->
                    

        </div> <!-- contenido -->
      </div> <!-- panel -->
    </div>
  </div>
</div>

<script type="text/javascript">init_DataTables();</script>