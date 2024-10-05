<?php
if (!isset($_SESSION['usuario'])) {
    header("Location: ../../../index.php");
}
if ($_SESSION['id_finca_usuario'] == '0') {
    session_destroy();
    header("Location: ../../../index.php");
}

$id_finca = $_SESSION['id_finca_usuario'];

function queryLabores($reporte = true)
{
    global $conexion;

    try {
        $query = "SELECT
                    tb_labor.id_labor AS id,
                    tb_labor.nombre AS nombre 
                FROM
                    tb_labor 
                ORDER BY
                    tb_labor.nombre ASC";
        $rs = mysqli_query($conexion, $query);

        if ($reporte) {
            $cantidad =  mysqli_num_rows($rs);
            while ($datos = mysqli_fetch_array($rs)) {
                $idlabores= $datos['id'];
                $labores = $datos['nombre'];

                echo utf8_encode('<option value='.$idlabores.'>'.$labores.'</option>');
            }
            return $cantidad;
        } else {
            return $rs;
        }
    } catch (Exception $e) {
        echo 'Message: ' . $e->getMessage();
    }
}

function queryPersonal($reporte = true,$tipo)
{
    global $conexion, $id_finca;

    try {

        switch ($tipo) {
            case 'propio':
                $consulta = '0';
                break;
            
            case 'tercero':
                $consulta = '1';
                break;
        }

        $query = "SELECT
                    tb_personal.id_personal AS id,
                    CONCAT( tb_personal.apellido, ', ', tb_personal.nombre ) AS nombre 
                FROM
                    tb_personal 
                WHERE
                    tb_personal.eventual = '$consulta'
                    AND tb_personal.id_finca = '$id_finca' 
                ORDER BY
                    tb_personal.apellido ASC";
        $rs = mysqli_query($conexion, $query);

        if ($reporte) {
            $cantidad =  mysqli_num_rows($rs);
            while ($datos = mysqli_fetch_array($rs)) {
                $idpersonal= $datos['id'];
                $personal = $datos['nombre'];

                echo utf8_encode('<option value='.$idpersonal.'>'.$personal.'</option>');
            }
            return $cantidad;
        } else {
            return $rs;
        }
    } catch (Exception $e) {
        echo 'Message: ' . $e->getMessage();
    }
}

function queryCampania()
{
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