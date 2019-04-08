<?php
include "$_SERVER[DOCUMENT_ROOT]/header.php";
session_start();
include "$_SERVER[DOCUMENT_ROOT]/topmain.php";

echo "<title>Kellotuseditori</title>\n";

$self = $_SERVER['PHP_SELF'];
$request = $_SERVER['REQUEST_METHOD'];

if (!isset($_SESSION['logged_in_user']) || $_SESSION['logged_in_user']->time_admin == 0) {
    echo "<script type='text/javascript' language='javascript'> window.location.href = '/loginpage.php';</script>";
    exit;
}


if ($request == 'GET') {
  $employee_query = tc_query("SELECT * FROM employees WHERE disabled = 0 ORDER BY displayname ASC");

echo '<section class="container">
        <div class="middleContent">
          <div class="box">
            <h2>Kellotuseditori - valitse työntekijä</h2>
            <div class="section">
            <form action="'.$self.'" method="post">
              <table style="width:60%;">';

  while ( $employee = mysqli_fetch_array($employee_query) ) {
    echo        '<tr>
                  <td>'.$employee[3].'</td>
                  <td>
                    <label class="container">
                      <input class="check" type="radio" name="emp" value="'.$employee[0].'">
                      <span class="checkmark"></span>
                    </label>
                  </td>
                </tr>';
  }
echo '          </table>
                <br>
                <button name="edittime" type="submit" class="btn" value=""><i class="fas fa-pencil-alt"></i> Valitse</button)
              </form>
            </div>
          </div>
        </div>
      </section>';
}


else if  ( isset($_POST['edittime']) ) {

  if ( !isset($_POST['emp']) ) {
    echo '<h2 style="color:red;">Virhe! Et valinnut työntekijää</h2>
          <a href="/timeeditor/time_editor.php">< Takaisin</a>';
    exit;
  }

  $empfullname = $_POST['emp'];
  $user_data = mysqli_fetch_row(tc_query( "SELECT * FROM employees WHERE empfullname = '$empfullname'"));

//  echo '<a class="btn back" href="/time_editor.php"> Takaisin</a>';
  echo '<section class="container">';
  echo '  <div class="leftContent">
          <div class="box">
            <h2>Lisää aika</h2>
            <div class="section">
              <form action="'.$self.'" method="post">
                <p>Täytä toinen tai molemmat:</p>
                <b>Sisään:</b>
                <br>
                <input type="text" id="from" autocomplete="off" size="10" maxlength="10" name="in_date" placeholder="pvm">
                <input name="in_time" type="time">
                <br><br>
                <b>Ulos:</b>
                <br>
                <input type="text" id="to" autocomplete="off" size="10" maxlength="10" name="out_date" placeholder="pvm">
                <input name="out_time" type="time">
                <br><br>
                <input type="text" name="emp" value="'.$empfullname.'" style="display:none;">
                <button type="submit" name="edittime" class="btn">Lisää</button>
              </form>
            </div>';
  if (isset($_POST['in_date']) || isset($_POST['out_date'])) {
    echo '  <div class="section">
              <p>Nämä lisättiin onnistuneesti:</p>';
    include 'addtime.php';
    echo '  </div>';
  }
  echo '  </div></div>';

  echo'    <div class="middleContent">
            <div class="box">
              <h2>Kellotuseditori - '.$user_data[3].'</h2>
              <div class="section">
              <form action="alter_time.php" method="post">
                Oransilla merkityt kirjaukset ilmaisevat virheestä.
                <button type="submit" name="deletetime" value="'.$empfullname.'" class="btn del" style="float:right; margin-bottom: 10px;">Poista valitut</button>
                <table>
                  <tr>
                    <th>Sisään/Ulos</th>
                    <th>Päivä</th>
                    <th>Kello</th>
                    <th colspan="3" >Viesti</th>
                    <th>Poista</th>
                    <th>Muokkaa</th>
                  </tr>';
  $max = 0;
  $prev = '';
  $inoutdata_query = tc_query("SELECT * FROM info WHERE fullname = '$empfullname' ORDER BY timestamp DESC");
  while ( $inout = mysqli_fetch_row($inoutdata_query) ) {

    $logTime = new DateTime("@$inout[3]");
    $logTime->setTimeZone(new DateTimeZone('Europe/Helsinki'));

    if ( $prev == '' || ($prev == 'out' && $inout[2] == 'in') || ($prev == 'in' && $inout[2] == 'out')) {
      if ($inout[2] == 'in') {
        echo "<tr style='background-color: white;'>
                <td style='text-align:center;'><span class='inout' style='background-color:var(--lightgreen); border-radius: 0 0 10px 10px; margin-top: -6px;'>$inout[2]</span></td>";
      } else {
        echo "<tr style='background-color: var(--light);'>
                <td style='text-align:center;'><span class='inout' style='background-color:var(--red); border-radius: 10px 10px 0 0; margin-bottom: -6px;'>$inout[2]</span></td>"; }
    }
    else {
      echo "<tr style='background-color: var(--orange);'>
              <td style='text-align:center;'><span>$inout[2]</span></td>"; }

      echo "  <td style='text-align:center;'>".$logTime->format("d.m.Y")."</td>
              <td style='text-align:center;'>".$logTime->format("H:i")."</td>
              <td colspan='3' style='word-wrap: break-word;'>$inout[4]</td>
              <td style='text-align:center;'>
                <label class='container'>
                  <input type='checkbox' name='deletelist[]' value='$inout[0]' class='check'>
                  <span class='checkmark'></span>
                </label>
              </td>
              <td style='text-align:center;'><button type='submit' name='edit' value='$inout[0]' class='btn'><i class='fas fa-pencil-alt'></i></button></td>
            </tr>";
    $prev = $inout[2];
    $max++;
    if ($max == 200) { break; }
  }
echo '          </table>
              </form>
              (näyttää viimeiset 200 kirjausta, mikäli vanhempia tarvitsee muokata tulee tämä tehdä phpMyAdminissa)
            </div>
          </div>
        </div>
      </section>';
}

?>
