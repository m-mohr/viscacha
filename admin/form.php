<?php
require('../classes/class.charts.php');
$PG = new PowerGraphic();

echo 
'<html>
<head>
<title>Power Graphic - by Carlos Reche</title>
<style type="text/css">
<!--

body {
    margin-top: 50px;
    margin-bottom: 30px;
    margin-left: 70px;
    margin-right: 70px;
    background-color: #ffffff;
}
body, table {
    font-size: 13px;
    font-family: verdana;
    color: #666666;
}
input, select {
    font-size: 11px;
    font-family: verdana;
    color: #333333;
    border: 1px solid #aaaaaa;
}

a:link    { color: #666666; font-weight: bold; text-decoration: none; }
a:visited { color: #666666; font-weight: bold; text-decoration: none; }
a:hover   { color: #cc0000; text-decoration: none; }
a:active  { color: #666666; text-decoration: none; }

-->
</style>
<script type="text/javascript">
<!--

var i = 4;

function add_parameter() {
    i++;
    space = ((i+1) < 10) ? "8px" : "0px";
    display_status = (document.form_data.graphic_2_values.checked == true) ? "" : "none";
    new_line  = "<div style=\"margin-left: " + space + "\"> " + (i+1) + ". <input type=\"text\" name=\"x" + i + "\" id=\"x" + i + "\" size=\"20\" /> ";
    new_line += " <input type=\"text\" name=\"y" + i + "\" id=\"y" + i + "\" size=\"10\" onkeypress=\"return numbers();\" /> ";
    new_line += " <input type=\"text\" name=\"z" + i + "\" id=\"z" + i + "\" size=\"10\" onkeypress=\"return numbers();\" style=\"display: " + display_status + ";\" /> </div>";
    document.getElementById("add_parameter").innerHTML += new_line;
}

function show_graphic_2_values() {
    var display_status = (document.form_data.graphic_2_values.checked == true) ? "" : "none";
    document.getElementById("value_2").style.display = display_status;
    for (var x = 0; x <= i; x++) {
        document.getElementById("z" + x).style.display = display_status;
    }
}

function numbers() {
    key = event.keyCode;
    if ((key >= 48 && key <= 57) || key == 46 || key == 13) { return true; } else { return false; }
}

//-->
</script>
</head>
<body>

<form action="../classes/class.charts.php" method="post" name="form_data" id="form_data"> <br />
  <div style="margin-bottom: 10px;">
    Title: <input type="text" name="title" id="title" size="30" style="margin: 0px 0px 0px 11px;" />
  </div>
  Axis X: <input type="text" name="axis_x" id="axis_x" size="30" /> <br />
  Axis Y: <input type="text" name="axis_y" id="axis_y" size="30" /> <br />

  <div style="margin: 10px 0px 10px 0px;">
    Graphic 1: <input type="text" name="graphic_1" id="graphic_1" style="width: 172px;" /> <br />
    Graphic 2: <input type="text" name="graphic_2" id="graphic_2" style="width: 172px;" /> <br />
  </div>

  Type: <select name="type" id="type" style="margin: 5px 0px 0px 7px;">';
        foreach ($PG->available_types as $code => $type) {
            echo '    <option value="' . $code . '"> ' . $type . ' </option>';
        }
        echo 
'  </select> <br />
Color: <select name="skin" id="skin" style="margin: 5px 0px 0px 7px;">';
        foreach ($PG->available_skins as $code => $color) {
            echo '    <option value="' . $code . '"> ' . $color . ' </option>';
        }
        echo 
'  </select>


  <div style="margin-top: 20px;" id="parameters">
    <div style="margin-bottom: 20px;">
      <input type="checkbox" name="graphic_2_values" id="graphic_2_values" value="1" onclick="javascript: show_graphic_2_values();" style="border-width: 0px;" /> Show values for Graphic 2
    </div>

    <span style="margin-left: 5px; font-size: 15px; font-family: arial;"><a href="javascript: add_parameter();">+</a></span>
    <span style="margin-left: 40px;">Parameter</span> 
    <span style="margin-left: 50px;">Value 1</span>  <span id="value_2" style="display: none; margin-left: 25px;">Value 2</span>';
        for ($i = 0; $i <= 4; $i++) {
            echo '<div style="margin-left: 8px;"> '.($i+1).'. <input type="text" name="x'.$i.'" id="x'.$i.'" size="20" /> ';
            echo ' <input type="text" name="y'.$i.'" id="y'.$i.'" size="10" onkeypress="return numbers();" /> ';
            echo ' <input type="text" name="z'.$i.'" id="z'.$i.'" size="10" onkeypress="return numbers();" style="display: none;" /> </div>';
        }
echo 
'    <div id="add_parameter"></div>
    <span style="margin-left: 5px; font-size: 15px; font-family: arial;"><a href="javascript: add_parameter();">+</a></span>
  </div>

  <input type="checkbox" name="credits" id="credits" value="1" checked="checked" style="border-width: 0px;" /> Show credits

  <input type="submit" value="Create" style="cursor: pointer; margin: 10px 0px 0px 60px;" />
</form>


</body>
</html>';
?>