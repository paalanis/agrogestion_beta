<?php
if (!isset($_SESSION['usuario'])) {
    header("Location: ../../../index.php");
}
if ($_SESSION['id_finca_usuario'] == '0') {
    session_destroy();
    header("Location: ../../../index.php");
}

include '../../conexion/conexion.php';

$id_finca = $_SESSION['id_finca_usuario'];
$conexion = conectarServidor();


function queryCampania(){
    global $conexion;

    try {
        $query = "SELECT
                    tb_campania.id_campania as id, 
                    tb_campania.nombre as nombre
                  FROM
                    tb_campania
                  ORDER BY
                    tb_campania.nombre ASC";
        $rs = mysqli_query($conexion, $query);

        return $rs;
    } catch (Exception $e) {
        echo 'Message: ' . $e->getMessage();
    }
}

function eliminarTabla($tabla){
    global $conexion;
    try{
        $queryEliminar = "DROP TABLE IF	EXISTS $tabla";
        mysqli_query($conexion, $queryEliminar);
    }
    catch(Exception $e){
        echo 'Message: ' .$e->getMessage();
    }
}

function calculaEjecutado($mesInicio,$mesFin,$anio,$campania){
    global $conexion,$id_finca;

        eliminarTabla("tb_ejecutado");
        try{
            $query = "CREATE TABLE tb_ejecutado(id_ejecutado INT UNSIGNED AUTO_INCREMENT PRIMARY KEY) AS
                        SELECT
                            tb_parte_diario.id_labor AS id_labor,
                            MONTH(tb_parte_diario.fecha) AS mes, 
                            YEAR(tb_parte_diario.fecha) AS anio,
                            tb_labor.nombre AS labor, 
                            IF(tb_labor.id_unidad = 19,ROUND(SUM(tb_parte_diario.horas_trabajadas)/8,3),ROUND(SUM(tb_parte_diario.horas_trabajadas),2)) AS valoresEjecutado, 
                            tb_unidad.nombre AS unidadMedida
                        FROM
                            tb_parte_diario
                            INNER JOIN
                            tb_labor
                            ON 
                                tb_parte_diario.id_labor = tb_labor.id_labor
                            INNER JOIN
                            tb_unidad
                            ON 
                                tb_labor.id_unidad = tb_unidad.id_unidad
                        WHERE
                            tb_parte_diario.id_finca = $id_finca AND
                            MONTH(tb_parte_diario.fecha) BETWEEN $mesInicio AND $mesFin AND
                            tb_parte_diario.id_campania = $campania
                        GROUP BY
                            tb_parte_diario.id_labor,
                            MONTH(tb_parte_diario.fecha)
                        ORDER BY
                            MONTH(tb_parte_diario.fecha) ASC,
                            id_labor ASC";
            mysqli_query($conexion, $query);

        }
        catch(Exception $e){
            echo 'Message: ' .$e->getMessage();
        }        
}

function calculaPrespuestado($mesInicio,$mesFin,$anio,$campania){
    global $conexion,$id_finca;
    
        eliminarTabla("tb_presupuestado");  
        try{
            $sqlEjecutado = "CREATE TABLE tb_presupuestado(id_presupuestado INT UNSIGNED AUTO_INCREMENT PRIMARY KEY) AS
                                SELECT
                                    tb_presupuesto_original.id_labor AS id_labor,
                                    tb_presupuesto_original.mes AS mes, 
                                    tb_presupuesto_original.anio AS anio,
                                    tb_labor.nombre AS labor, 
                                    IF(tb_labor.id_unidad = 19,ROUND(SUM(tb_presupuesto_original.aplicacion)/8,3),ROUND(SUM(tb_presupuesto_original.aplicacion),2)) AS valoresPresupuestado,
                                    tb_unidad.nombre AS unidadMedida
                                FROM
                                    tb_presupuesto_original
                                    INNER JOIN
                                    tb_labor
                                    ON 
                                        tb_presupuesto_original.id_labor = tb_labor.id_labor
                                    INNER JOIN
                                    tb_unidad
                                    ON 
                                        tb_labor.id_unidad = tb_unidad.id_unidad
                                WHERE
                                    tb_presupuesto_original.id_finca = $id_finca AND
                                    tb_presupuesto_original.mes BETWEEN $mesInicio AND $mesFin AND
                                    tb_presupuesto_original.id_campania = $campania
                                GROUP BY
                                    tb_presupuesto_original.id_labor,
                                    tb_presupuesto_original.mes
                                ORDER BY
                                    tb_presupuesto_original.mes ASC,
                                    id_labor ASC";
            mysqli_query($conexion, $sqlEjecutado);

        }
        catch(Exception $e){
            echo 'Message: ' .$e->getMessage();
        }        
}

function calculaControlPrespuestario(){
    global $conexion,$id_finca;
    
        eliminarTabla("tb_control_presupuestario");  
        try{
            $query =    "CREATE TABLE tb_control_presupuestario(
                        id_control_presupuestario INT UNSIGNED AUTO_INCREMENT PRIMARY KEY) AS
                        SELECT
                            id_labor,
                            mes, 
                            anio, 
                            labor as labores, 
                            SUM(valoresPresupuestado) AS total_presupuestado, 
                            SUM(valoresEjecutado) AS total_ejecutado,
                            unidadMedida AS unidades
                        FROM (
                            SELECT id_labor, mes, anio, labor, valoresPresupuestado, NULL AS valoresEjecutado, unidadMedida
                            FROM tb_presupuestado
                            UNION ALL
                            SELECT id_labor, mes, anio, labor, NULL, valoresEjecutado, unidadMedida
                            FROM tb_ejecutado
                        ) AS combined_data
                        GROUP BY id_labor, mes, anio, labor";
            mysqli_query($conexion, $query);

        }
        catch(Exception $e){
            echo 'Message: ' .$e->getMessage();
        }        
}

function procesaControlPrespuestario($mesInicio,$mesFin,$anio,$campania){
    
        calculaEjecutado($mesInicio,$mesFin,$anio,$campania);
        calculaPrespuestado($mesInicio,$mesFin,$anio,$campania);
        calculaControlPrespuestario();

}

function reporteControlPresupuestario(){
    global $conexion;

    try {
        $query = "SELECT
                        tb_control_presupuestario.id_control_presupuestario AS id,
                        tb_control_presupuestario.id_labor AS id_labor,
                        tb_control_presupuestario.labores AS labores, 
                        tb_control_presupuestario.total_presupuestado AS total_presupuestado, 
                        tb_control_presupuestario.total_ejecutado AS total_ejecutado, 
                        tb_control_presupuestario.unidades AS unidades
                    FROM
                        tb_control_presupuestario
                    ORDER BY
                        total_presupuestado DESC, 
                        labores ASC";
        $rs = mysqli_query($conexion, $query);
        $cantidad =  mysqli_num_rows($rs);

        while ($datos = mysqli_fetch_array($rs)){
            $id=$datos['id'];
            $id_labor=$datos['id_labor'];
            $labores=$datos['labores'];
            $total_presupuestado=$datos['total_presupuestado'];
            $total_ejecutado=$datos['total_ejecutado'];
            $unidades=$datos['unidades'];
            
            echo '<tr class="even pointer">
              <td id="'.$id.'">'.$id_labor.'</td>
              <td id="'.$id.'">'.$labores.'</td>
              <td id="'.$id.'">'.$total_presupuestado.'</td>
              <td id="'.$id.'">'.$total_ejecutado.'</td>
              <td id="'.$id.'">'.$unidades.'</td>
            </tr>';
        }
        
        return $cantidad;
    } catch (Exception $e) {
        echo 'Message: ' . $e->getMessage();
    }
}

function reporteEjecutado($reporte = true){
    global $conexion;

    try {
        $query = "SELECT
                    tb_ejecutado.id_ejecutado AS id, 
                    tb_ejecutado.id_labor AS id_labor, 
                    tb_ejecutado.mes AS mes, 
                    tb_ejecutado.anio AS anio, 
                    tb_ejecutado.labor AS labores, 
                    tb_ejecutado.valoresEjecutado AS total_ejecutado, 
                    tb_ejecutado.unidadMedida AS unidades
                FROM
                    tb_ejecutado
                ORDER BY
                    tb_ejecutado.mes ASC";
        $rs = mysqli_query($conexion, $query);

        if ($reporte) {
            $cantidad =  mysqli_num_rows($rs);
            while ($datos = mysqli_fetch_array($rs)) {
                $id = $datos['id'];
                $id_labor = $datos['id_labor'];
                $mes = $datos['mes'];
                $anio = $datos['anio'];
                $labores = $datos['labores'];
                $total_ejecutado = $datos['total_ejecutado'];
                $unidades = $datos['unidades'];

                echo '<tr class="even pointer">
              <td id="' . $id . '">' . $id_labor . '</td>
              <td id="' . $id . '">' . $mes . '</td>
              <td id="' . $id . '">' . $anio . '</td>
              <td id="' . $id . '">' . $labores . '</td>
              <td id="' . $id . '">' . $total_ejecutado . '</td>
              <td id="' . $id . '">' . $unidades . '</td>
            </tr>';
            }
            return $cantidad;
        }else{
            return $rs;
        }
    } catch (Exception $e) {
        echo 'Message: ' . $e->getMessage();
    }
}

function reportePresupuestado($reporte = true){
    global $conexion;

    try {
        $query = "SELECT
                    tb_presupuestado.id_presupuestado AS id, 
                    tb_presupuestado.id_labor AS id_labor, 
                    tb_presupuestado.mes AS mes, 
                    tb_presupuestado.anio AS anio, 
                    tb_presupuestado.labor AS labores, 
                    tb_presupuestado.valoresPresupuestado AS total_presupuestado, 
                    tb_presupuestado.unidadMedida AS unidades
                FROM
                    tb_presupuestado
                ORDER BY
                    tb_presupuestado.mes ASC";
        $rs = mysqli_query($conexion, $query);

        if ($reporte) {
            $cantidad =  mysqli_num_rows($rs);
            while ($datos = mysqli_fetch_array($rs)) {
                $id = $datos['id'];
                $id_labor = $datos['id_labor'];
                $mes = $datos['mes'];
                $anio = $datos['anio'];
                $labores = $datos['labores'];
                $total_presupuestado = $datos['total_presupuestado'];
                $unidades = $datos['unidades'];

                echo '<tr class="even pointer">
              <td id="' . $id . '">' . $id_labor . '</td>
              <td id="' . $id . '">' . $mes . '</td>
              <td id="' . $id . '">' . $anio . '</td>
              <td id="' . $id . '">' . $labores . '</td>
              <td id="' . $id . '">' . $total_presupuestado . '</td>
              <td id="' . $id . '">' . $unidades . '</td>
            </tr>';
            }
            return $cantidad;
        } else {
            return $rs;
        }
    } catch (Exception $e) {
        echo 'Message: ' . $e->getMessage();
    }
}

?>