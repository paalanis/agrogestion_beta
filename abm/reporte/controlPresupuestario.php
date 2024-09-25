<?php
session_start();
include '../querys/presupuesto.php';

$mes = $_REQUEST['dato_mes'];
$campania = $_REQUEST['dato_campania'];
$version = $_REQUEST['dato_version'];

procesaControlPrespuestario($mes,$mes,'2024',$campania);

?>

<div class="">

  <div class="clearfix"></div>
  <div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">

      <div class="x_content">

        <table id="datatable-responsive" class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
          <thead>
            <tr>
              <th>IdLabor </th>
              <th>Labor </th>
              <th>Presupuestado </th>
              <th>Ejecutado </th>
              <th>Unidad </th>
              </th>
            </tr>
          </thead>

          <tbody>

            <?php

            $cantidad =  reporteControlPresupuestario();
            if ($cantidad > 0) { // si existen control con de esa finca se muestran, de lo contrario queda en blanco  

            ?>
              <script type="text/javascript">
                document.getElementById("botonExcel1").style.visibility = "visible";
              </script>
            <?php

            }
            ?>
          </tbody>
        </table>
        <?php
        if ($cantidad == 0) {

          // echo "No se encontraron registros con el filtro seleccionado";
        ?>
          <script type="text/javascript">
            document.getElementById("botonExcel1").style.visibility = "hidden";
          </script>
        <?php
        }
        ?>

      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
  init_DataTables();

  $(function() {
    $('#dato_mes').change(function() {

      document.getElementById("botonExcel1").style.visibility = "hidden";

    })
  })
  $(function() {
    $('#dato_campania').change(function() {

      document.getElementById("botonExcel1").style.visibility = "hidden";

    })
  })
  $(function() {
    $('#dato_version').change(function() {

      document.getElementById("botonExcel1").style.visibility = "hidden";

    })
  })
</script>